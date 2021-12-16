<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseFile;
use App\Models\Featured;
use App\Models\TopCourse;
use Illuminate\Http\Request;
use Aws\S3\S3Client;
use Carbon\Carbon;

class CourseController extends Controller
{

    /**
     * @OA\Get(
     *     path="/admin/courses/approved",
     *     summary="Get Approved Courses ",
     *     tags={"Admin Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Approved Courses retrieved successfully",
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
    public function approved()
    {
        return $this->getCourses(course::APPROVED);
    }

    /**
     * @OA\Get(
     *     path="/admin/courses/pending",
     *     summary="Get Pending Courses ",
     *     tags={"Admin Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Pending Courses retrieved successfully",
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
    public function pending()
    {
        return $this->getCourses(course::PENDING);
    }

    /**
     * @OA\Get(
     *     path="/admin/courses/rejected",
     *     summary="Get Rejected Courses ",
     *     tags={"Admin Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Rejected Courses retrieved successfully",
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
    public function rejected()
    {

        return $this->getCourses(course::REJECTED);
    }


    /**
     * @OA\Get(
     *     path="/admin/courses/deleted",
     *     summary="Get Deleted Courses ",
     *     tags={"Admin Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Courses Deleted by tutors retrieved successfully",
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
    public function deleted()
    {
        $courses = Course::onlyTrashed()->paginate(50);
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
     * @OA\Patch(
     *     path="/admin/courses/revert",
     *     summary="Revert Tutor Deleted Course",
     *     tags={"Admin Courses"},
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
     *         description="Course reverted successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation Error",
     *         @OA\JsonContent()
     *     ),
     * @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function revertDelete(Request $request)
    {
        Course::onlyTrashed()->find($request->id)->restore();
        return $this->success();
    }


    /**
     * @OA\Patch(
     *     path="/admin/courses/update",
     *     summary="Update course status",
     *     tags={"Admin Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation Error",
     *         @OA\JsonContent()
     *     ),
     * @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        Course::findOrFail($request->id)->update([
            'status' => $request->status
        ]);
        return $this->success();
    }

    /**
     * @OA\Delete(
     *     path="/admin/courses/delete/{id}",
     *     summary="Delete a course",
     *     tags={"Admin Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     * *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course Deleted successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action.",
     *     )
     * )
     *   * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $course = Course::withTrashed()->find($id);
        $s3 = config('filesystems.disks.s3');
        $client =
            new S3Client([
                'version' => 'latest',
                'region' => $s3['region'],
                'credentials' => [
                    'key' =>  $s3['key'],
                    'secret' =>  $s3['secret'],
                ]
            ]);
        $bucket =  $s3['bucket'];

        return $this->destroyNow($course, $client, $bucket);
    }

    public function deleteTest(Request $request)
    {
        $client = $request->client;
        $bucket = $request->bucket;
        $course = $request->course;

        return $this->destroyNow($course, $client, $bucket);
    }

    public function destroyNow($course, $client, $bucket)
    {

        try {

            // delete all course files
            $files = CourseFile::whereCourseId($course->id)->get();
            if (count($files) > 0) {
                foreach ($files as $file) {
                    $key =  substr($file->url, strpos($file->url, ".com/") + 5);
                    $client->deleteObject([
                        'Bucket' => $bucket,
                        'Key'    => $key
                    ]);
                    $file->delete();
                }
            }
            // remove course image and delete course
            if ($course->image_url !== null) {
                $key =  substr($course->image_url, strpos($course->image_url, ".com/") + 5);
                $client->deleteObject([
                    'Bucket' => $bucket,
                    'Key'    => $key
                ]);
            }
            $course->forceDelete();
            return $this->success();
        } catch (\Throwable $th) {
            //throw $th;
            return $this->error($th);
        }
    }

    public function getCourses($status, $x = null)
    {
        $courses = Course::whereStatus($status)->paginate(10);
        if ($x != null) {
            $courses = $x;
        }
        foreach ($courses as $course) {
            $course->pricing = intval($course->pricing);
            $course->students = $course->students()->get();
            $course->total_students = count($course->students);
            $course->instructors = json_decode($course->instructors);
            $course->date = Carbon::parse($course->created_at)->format('d-m-Y H:i:s');
            $course->reviews;
            $course->rating = $course->reviews->average('rating');
            foreach ($course->sections as $section) {
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

    public function addToFeatured(Request $request)
    {
        $courses = $request->courses;
        foreach ($courses as $course) {
            Featured::firstOrCreate([
                'course_id' => $course
            ]);
        }
        return $this->success();
    }


    public function featuredCourses()
    {
        $course_ids = Featured::all()->pluck('course_id');
        $courses = Course::whereIn('id', $course_ids)->paginate(10);
        return $this->getCourses(null, $courses);
    }

    public function topCourses()
    {

        $top_courses = TopCourse::all()->pluck('course_id');
        $courses = Course::whereIn('id', $top_courses)->paginate(10);
        return $this->getCourses(null, $courses);
    }

    public function removeFromFeatured(Request $request)
    {

        Featured::whereIn('course_id', $request->courses)->delete();
        return $this->success();
    }

    public function addToTopRated(Request $request)
    {
        $courses = $request->courses;
        foreach ($courses as $course) {
            TopCourse::firstOrCreate([
                'course_id' => $course
            ]);
        }
        return $this->success();
    }
    public function removeFromTopRated(Request $request)
    {
        TopCourse::whereIn('course_id', $request->courses)->delete();
        return $this->success();
    }
}
