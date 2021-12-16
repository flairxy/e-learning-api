<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\TopCourse;
use Illuminate\Http\Request;

class CategoryController extends Controller
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
     * @OA\Get(
     *     path="/categories",
     *     summary="Get all categories ",
     *     tags={"Category"},
     *     @OA\Response(
     *         response="200",
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $categories = Category::all();
        return $this->success($categories);
    }

    public function categoryCourses()
    {
        $categories = Category::all();
        $top_courses = TopCourse::get()->pluck('course_id');
        foreach ($categories as $category) {

            $courses = Course::whereIn('id', $top_courses)->whereCategoryId($category->id)->get();
            foreach ($courses as $course) {
                $course->instructors = json_decode($course->instructors);
            }
            $category->courses = $courses;
            $category->total_courses = count($courses);
        }

        return $this->success($categories);
    }

    /**
     * @OA\Post(
     *     path="/categories/create",
     *     summary="Create Category",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Category name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Category created successfully",
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
            'name' => ['required', 'string'],
        ]);

        Category::create($request->all());
        return $this->success();
    }


    /**
     * @OA\Post(
     *     path="/categories/update",
     *     summary="Update Category",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Category id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Category name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Category updated successfully",
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
        $this->validate($request, [
            'name' => ['required', 'string'],
        ]);
        Category::findOrFail($request->id)->update($request->all());
        return $this->success();
    }

    /**
     * @OA\Delete(
     *     path="/categories/delete/{id}",
     *     summary="Delete Category",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Category id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Category deleted successfully",
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
    public function destroy($id)
    {
        Category::findOrFail($id)->delete();
        return $this->success();
    }
}
