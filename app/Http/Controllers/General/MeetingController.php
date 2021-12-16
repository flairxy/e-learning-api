<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Content;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\Section;
use App\Traits\ZoomJWT;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    use ZoomJWT;

    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;

    /**
     * @OA\Post(
     *     path="/meetings/create",
     *     summary="Create Meeting",
     *     tags={"Meeting"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="topic",
     *         in="query",
     *         description="Meeting topic",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         description="Meeting start time",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="duration",
     *         in="query",
     *         description="Meeting duration (in minutes)",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="agenda",
     *         in="query",
     *         description="Meeting agenda",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Meeting created successfully",
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
    public function create(Request $request)
    {
        $this->validate($request, [
            'topic' => 'required|string',
            'start_time' => 'required|integer',
            'duration' => 'required|integer',
            'agenda' => 'string|nullable',
        ]);

        $path = 'users/me/meetings';
        $response = $this->zoomPost($path, [
            'topic' => $request->topic,
            'type' => self::MEETING_TYPE_SCHEDULE,
            'start_time' => Carbon::now()->addMinutes($request->start_time),
            'duration' => $request->duration,
            'agenda' => $request->agenda,
            'timezone' => 'Africa/Bangui',
            'settings' => [
                'host_video' => false,
                'participant_video' => false,
                'waiting_room' => true,
            ]
        ]);

        return $this->createMeeting($response, $request->course_id);
    }

    public static function testCreateMeeting($response, $course)
    {
        return (new self)->createMeeting($response, $course);
    }

    public function createMeeting($response, $course)
    {
        $meeting = CourseMeeting::updateOrCreate(
            ['course_id' => $course],
            [
                'join_url' => $response->join_url,
                'password' => $response->password,
                'meeting_id' => $response->id,
                'topic' => $response->topic,
                'course_id' => $course,
            ]
        );
        return $this->success($response);
    }



    /**
     * @OA\Get(
     *     path="/meetings/{id}",
     *     summary="Get Meeting",
     *     tags={"Meeting"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="topic",
     *         in="query",
     *         description="Meeting topic",
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
     *
     *     @OA\Response(
     *         response="200",
     *         description="Meeting retrieved successfully",
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
    public function get($id)
    {
        $meeting = CourseMeeting::whereCourseId($id)->first();
        return $this->success($meeting);
    }


    /**
     * @OA\Delete(
     *     path="/meetings/delete/{id}",
     *     summary="Delete Meeting",
     *     tags={"Meeting"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="meeting_id",
     *         in="query",
     *         description="Meeting id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Meeting deleted successfully",
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
    public function delete($id)
    {

        $meeting = CourseMeeting::whereMeetingId($id)->first();
        $path = 'meetings/' . $meeting->meeting_id;
        $this->zoomDelete($path);

        return $this->deleteMeeting($meeting);
    }

    public static function testDeleteMeeting($meeting)
    {
        return (new self)->deleteMeeting($meeting);
    }

    public function deleteMeeting($meeting)
    {

        $meeting->delete();
        return $this->success('meeting deleted');
    }
}
