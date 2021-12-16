<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\CourseFile;
use App\Models\Featured;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
     *     path="/courses/search",
     *     summary="Search courses by title",
     *     tags={"Courses"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Course title",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Courses retrieved successfully",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->search;
        $courses = Course::where('title', 'LIKE', '%' . $query . '%')->get();

        return $this->success($courses);
    }


    /**
     * @OA\Post(
     *     path="/courses/categories",
     *     summary="Get courses by category",
     *     tags={"Courses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Category id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Courses retrieved successfully",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseByCategory(Request $request)
    {
        $category = $request->id;
        $courses = Course::whereCategoryId($category)->get();
        foreach ($courses as $course) {
            $course->instructors = json_decode($course->instructors);
        }

        return $this->success($courses);
    }

    public function featuredCourses()
    {
        $course_ids = Featured::all()->pluck('course_id');
        $courses = Course::whereIn('id', $course_ids)->get();
        foreach ($courses as $course) {
            $course->instructors = json_decode($course->instructors);
        }
        return $this->success($courses);
    }

    /**
     * @OA\Get(
     *     path="/courses",
     *     summary="Get all approved courses ",
     *     tags={"Courses"},
     *     @OA\Response(
     *         response="200",
     *         description="Courses retrieved successfully",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $courses = Course::whereStatus(Course::APPROVED)->paginate(10);
        foreach ($courses as $course) {
            $course->pricing = intval($course->pricing);
            $course->students = $course->students()->get();
            $course->instructors = json_decode($course->instructors);

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
     *     path="/courses/show",
     *     summary="Get course by id",
     *     tags={"Courses"},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course retrieved successfully",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {

        $course = Course::findOrFail($request->course_id);
        $course->last_update = Carbon::parse($course->updated_at)->format('d/m/Y');
        $course->reviews;
        $course->students = $course->students()->get();
        $course->rating = $course->reviews->average('rating');
        foreach ($course->sections as $section) {
            $section->contents;
            foreach ($section->contents as $content) {
                $content->video = CourseFile::whereContentId($content->id)->whereVideo(true)->whereStatus(CourseFile::ACTIVE)->first();
            }
        }
        return $this->success($course);
    }



    /**
     * @OA\Post(
     *     path="/courses/contents",
     *     summary="Get course files",
     *     tags={"Courses"},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course retrieved successfully",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function contents(Request $request)
    {

        $contents = CourseFile::whereCourseId($request->course_id)->get();
        return $this->success($contents);
    }


    /**
     * @OA\Post(
     *     path="/courses/instructor",
     *     summary="Get instructor",
     *     tags={"Courses"},
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="Instructor username",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User retrieved successfully",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInstructor(Request $request)
    {
        $user = User::whereUsername($request->username)->first();
        if ($user) {
            $instructor = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
            ];
            return $this->success($instructor);
        } else {
            return $this->error('User not found');
        }
    }
}
