<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\CourseFile;
use App\Models\Tag;
use Exception;
use Hamcrest\Arrays\IsArray;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public function ping()
    {
        return $this->success(['name' => config('app.name')]);
    }


    /**
     * @OA\Post(
     *     path="/instructor/course",
     *     summary="Get instructor Courses ",
     *     tags={"Instructor Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="created_by",
     *         in="query",
     *         description="Instructor Id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Courses retrieved successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */

    public function index(Request $request)
    {

        $courses = Course::whereCreatedBy($request->created_by)->paginate(20);
        foreach ($courses as $course) {
            $course->pricing = intval($course->pricing);
            $course->students = $course->students()->get();
            foreach ($course->sections as $section) {
                $course->reviews;
                $course->rating = $course->reviews->average('rating');
                $section->contents;
            }
        }
        $response = [
            'pagination' => [
                'total' => $courses->total(),
                'per_page' => $courses->perPage(),
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem()
            ],
            'data' => $courses
        ];
        return $this->success($response);
    }


    /**
     * @OA\Post(
     *     path="/instructor/course/create",
     *     summary="Create Course",
     *     tags={"Instructor Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Course Title",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Course category id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="created_by",
     *         in="query",
     *         description="User id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Courses created successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'string'],
            'category' => ['required', 'integer'],
        ]);

        DB::beginTransaction();

        try {
            $data = $request->except('category');
            $data['category_id'] = $request->category;
            $course = Course::create($data);
            DB::commit();
            return $this->success($course);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollback();
        }
    }


    /**
     * @OA\Post(
     *     path="/instructor/course/show",
     *     summary="Get course by id",
     *     tags={"Instructor Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course retrieved successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $course = Course::findOrFail($request->id);
        $course->tags = $course->tags()->pluck('tag_id');
        return $this->success($course);
    }

    /**
     * @OA\Patch(
     *     path="/instructor/course/update",
     *     summary="Update Course",
     *     tags={"Instructor Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Courses updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request)
    {
        $course = Course::whereId($request->course_id)->first();
        $data = $request->except('tags', 'course_id', 'category');
        $data['category_id'] = $request->category ?? $course->category_id;
        $course->update($data);

        if (is_array($request->tags)) {
            $oldTags = $course->tags()->get();
            $tags = Tag::whereIn('id', $request->tags)->get();


            // remove previous tags
            if (count($oldTags) > 0) {
                foreach ($oldTags as $tag) {
                    $tag->courses()->detach($course);
                }
            }

            foreach ($tags as $tag) {
                $tag->courses()->attach($course);
            }
        }

        return $this->success($course);
    }


    /**
     * @OA\Patch(
     *     path="/instructor/course/files/update",
     *     summary="Update Course File",
     *     tags={"Instructor Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="content_id",
     *         in="query",
     *         description="Content id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="S3 file url",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="'video' or 'document'",
     *         in="query",
     *         description="File type. This can be 'video' or 'document'",
     *         required=true,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Courses updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateCourseFile(Request $request)
    {
        $course = CourseFile::whereUrl($request->url)->first();
        $course->status = true;
        $course->save();
        return $this->success();
    }



    /**
     * @OA\Delete(
     *     path="/instructor/course/delete/{id}/{created_by}",
     *     summary="Delete Course",
     *     tags={"Instructor Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="created_by",
     *         in="query",
     *         description="User id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Courses deleted successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, $created_by)
    {
        $course = Course::findOrFail($id)->whereCreatedBy($created_by)->first();
        $course->delete();
        return $this->success();
    }
}
