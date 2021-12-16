<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Content;
use App\Models\Course;
use App\Models\Review;
use App\Models\Section;
use App\Models\Transaction;
use App\Models\UserCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
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
     *     path="/instructor/performance/overview",
     *     summary="Instructor performance overview",
     *     tags={"Instructor Performance"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         description="User id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data retrieved successfully",
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
    public function overview(Request $request)
    {
        $user = User::findOrFail($request->user);
        $ids = Course::whereCreatedBy($user->id)->whereStatus(course::APPROVED)->pluck('id');

        $earnings = Transaction::whereIn('course_id', $ids)->whereStatus(true)->get();
        $totalEarnings = $earnings->sum('net_amount');
        $currentMonth = Carbon::now()->format('m');
        $monthEarnings = DB::table('transactions')->whereUserId($user->id)->whereStatus(true)->whereMonth('created_at', '=', $currentMonth)->sum('net_amount');


        $student = UserCourse::whereIn('course_id', $ids)->whereStatus(true)->get();
        $monthEnrollments = DB::table('user_courses')->whereUserId($user->id)->whereMonth('created_at', '=', $currentMonth)->get();

        $ratings = Review::whereIn('course_id', $ids)->average('rating');
        $monthRatings = DB::table('course_reviews')->whereUserId($user->id)->whereMonth('created_at', '=', $currentMonth)->average('rating');

        $data = [
            'total_earnings' => $totalEarnings,
            'month_earnings' => $monthEarnings,
            'total_enrollments' => count($student),
            'month_enrollments' => count($monthEnrollments),
            'ratings' => number_format((float) $ratings, 2, '.', ''),
            'month_ratings' => number_format((float) $monthRatings, 2, '.', ''),
        ];
        return $this->success($data);
    }


    /**
     * @OA\Post(
     *     path="/instructor/performance/students",
     *     summary="Instructor students",
     *     tags={"Instructor Performance"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         description="User id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data retrieved successfully",
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
    public function students(Request $request)
    {
        $user = User::findOrFail($request->user);
        $ids = Course::whereCreatedBy($user->id)->whereStatus(course::APPROVED)->pluck('id');
        $student_ids = UserCourse::whereIn('course_id', $ids)->whereStatus(true)->pluck('user_id');
        $students = User::whereIn('id', $student_ids)->get();
        return $this->success($students);
    }


    /**
     * @OA\Post(
     *     path="/instructor/performance/reviews",
     *     summary="Instructor reviews",
     *     tags={"Instructor Performance"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         description="User id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data retrieved successfully",
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
    public function reviews(Request $request)
    {
        $user = User::findOrFail($request->user);
        $ids = Course::whereCreatedBy($user->id)->whereStatus(course::APPROVED)->pluck('id');
        $reviews = Review::whereIn('course_id', $ids)->get();
        foreach ($reviews as $review) {
            $course = Course::findOrFail($review->course_id);
            $user = User::findOrFail($review->user_id);
            $review->student = $user->first_name . ' ' . $user->last_name;
            $review->course = $course->title;
        }
        return $this->success($reviews);
    }
}
