<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\CourseFile;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\UserCourse;
use App\Notifications\Message;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

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

    /**
     * @OA\Get(
     *     path="/",
     *     tags={"Extras"},
     *     summary="Ping",
     *     description="For Heath check: perfect for testing service health in production",
     *     @OA\Response(
     *         response="200",
     *         description="Service is active",
     *         @OA\JsonContent()
     *     ),
     *      @OA\Response(
     *         response="503",
     *         description="Service unavailable",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function ping()
    {
        return $this->success(['name' => config('app.name')]);
    }


    /**
     * @OA\Post(
     *     path="/student/course",
     *     summary="Get Student courses",
     *     tags={"Student Courses"},
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
     *         description="User id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course review successful",
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

    public function index(Request $request)
    {
        $user = User::findOrFail($request->id);
        foreach ($user->studentCourses as $course) {
            $course->instructors = json_decode($course->instructors);
        }

        return $this->success($user->studentCourses);
    }



    /**
     * @OA\Post(
     *     path="/student/course/show",
     *     summary="Get Student course",
     *     tags={"Student Courses"},
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
     *         description="User id",
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
     *         description="Course retrieved successful",
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
        $user = User::findOrFail($request->id);
        $course = $user->studentCourses->find($request->course_id);
        if ($course) {
            $course->review = Review::whereUserId($user->id)->whereCourseId($request->course_id)->first();
            foreach ($course->sections as $section) {
                $section->contents;
                foreach ($section->contents as $content) {
                    // $content->files;
                    $content->video = CourseFile::whereContentId($content->id)->whereVideo(true)->whereStatus(CourseFile::ACTIVE)->first();
                    $content->documents = CourseFile::whereContentId($content->id)->whereDocument(true)->whereStatus(CourseFile::ACTIVE)->get();
                }
            }

            return $this->success($course);
        }
        return $this->success();
    }

    public function callback(Request $request)
    {
        $paymentDetails = $request->all();
        $fixed_charge = Setting::whereName('FIXED_CHARGE')->first();
        $per_charge = Setting::whereName('PERCENT_CHARGE')->first();
        $merchant_charge = 0;
        if ($fixed_charge) {
            $merchant_charge = $fixed_charge->value;
        }
        if ($per_charge) {
            $merchant_charge = ($per_charge->value / 100) * $request->amount;
        }

        $net_amount = $request->amount - $merchant_charge;
        // return $request->all();
        if ($request->type == 'initial') {

            $data = $paymentDetails;
            $user = User::whereEmail($data['email'])->first();
            $single_data['user'] = $user->id;
            $single_data['course_id'] = $data['course_id'];
            $single_data['amount'] = (float) $data['amount'];
            $single_data['merchant_fee'] =  $merchant_charge;
            $single_data['net_amount'] =  $net_amount;
            $single_data['reference'] = $data['reference'];
            $single_data['role'] = $request->role;
            if ($request->role == 'free') {
                $single_data['reference'] = 'FREE-COURSE';
                $single_data['status'] = true;
            }
            return $this->createTransaction($single_data);
        }

        if (!$request->type) {

            if ($paymentDetails['status'] === 'success' && $paymentDetails['message'] === 'Approved') {
                $response = Transaction::whereTransactionId($paymentDetails['reference'])->first();
                $response->update([
                    'status' => true
                ]);

                return $this->userCourse($response);
            } else {
                return $this->error('payment was not successful');
            }
        }
    }

    public function userCourse($response)
    {

        UserCourse::create([
            'transaction_id' => $response['transaction_id'],
            'course_id' => $response['course_id'],
            'user_id' => $response['user_id'],
            'status' => true
        ]);

        $course = Course::findOrFail($response['course_id']);
        $user = User::findOrFail($response['user_id']);
        Notification::send($user, new Message($course->welcome_message, $course->created_by));

        return $this->success('transaction successful');
    }

    public function createTransaction($data)
    {

        $transaction['user_id'] = $data['user'];
        $transaction['course_id'] = $data['course_id'];
        $transaction['amount'] = (float) $data['amount'];
        $transaction['transaction_id'] = $data['reference'];
        $transaction['net_amount'] = $data['net_amount'];
        $transaction['merchant_fee'] = $data['merchant_fee'];
        $tr_create = Transaction::create($transaction);
        if ($data['role'] == 'free') {
            return $this->userCourse($tr_create);
        } else {

            return $tr_create;
        }
    }

    /**
     * @OA\Post(
     *     path="/student/course/review",
     *     summary="Student review course",
     *     tags={"Student Courses"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="User id",
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
     *         name="rating",
     *         in="query",
     *         description="rating",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="body",
     *         in="query",
     *         description="Review text",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Course review successful",
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
    public function review(Request $request)
    {
        Review::updateOrCreate([
            'course_id' => $request->course_id, 'user_id' => $request->user_id
        ], $request->all());
        return $this->success();
    }
}
