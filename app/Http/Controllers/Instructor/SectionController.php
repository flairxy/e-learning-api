<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Content;
use App\Models\Course;
use App\Models\CourseFile;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
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
     *     path="/instructor/section/show",
     *     summary="Instructor course sections",
     *     tags={"Instructor Section"},
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
     *         description="Course sections retrieved successfully",
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
        $sections = Section::whereCourseId($request->course_id)->orderBy('id')->get();
        foreach ($sections as $section) {
            $section->contents;
        }
        return $this->success($sections);
    }

    /**
     * @OA\Get(
     *     path="/instructor/{id}/{section}",
     *     summary="Instructor course sections",
     *     tags={"Instructor Section"},
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
     *         name="section",
     *         in="query",
     *         description="Section id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course sections retrieved successfully",
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
    public function getSection($id, $section)
    {
        $section = Section::whereCourseId($id)->whereId($section)->first();
        foreach ($section->contents as $content) {
            $content->video = CourseFile::whereContentId($content->id)->whereVideo(true)->whereStatus(CourseFile::ACTIVE)->first();
            $content->documents = CourseFile::whereContentId($content->id)->whereDocument(true)->whereStatus(CourseFile::ACTIVE)->get();
        }
        return $this->success($section);
    }


    /**
     * @OA\Post(
     *     path="/instructor/section/create",
     *     summary="Instructor create course section",
     *     tags={"Instructor Section"},
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
     *         name="name",
     *         in="query",
     *         description="Section name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course section created successfully",
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
            'name' => ['required', 'string',],
            'course_id' => ['required', 'string',],
        ]);

        $section = Section::create($request->all());
        return $this->success($section);
    }


    /**
     * @OA\Post(
     *     path="/instructor/section/update",
     *     summary="Instructor update course section",
     *     tags={"Instructor Section"},
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
     *         description="Section id",
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
     *         name="name",
     *         in="query",
     *         description="Section name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course section updated successfully",
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
        Section::whereId($request->id)->whereCourseId($request->course_id)->update($request->all());
        return $this->success();
    }



    /**
     * @OA\Delete(
     *     path="/instructor/section/delete/{id}/{course}",
     *     summary="Instructor delete course section",
     *     tags={"Instructor Section"},
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
     *         description="Section id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="course",
     *         in="query",
     *         description="Course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Section name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course section delete successfully",
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
    public function destroy($id, $course)
    {
        $section = Section::whereCourseId($course)->whereId($id)->first();
        $section->delete();

        return $this->success();
    }
}
