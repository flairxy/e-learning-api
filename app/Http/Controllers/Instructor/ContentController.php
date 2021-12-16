<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Auth\User;
use App\Models\Content;
use App\Models\Course;
use App\Models\CourseFile;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Aws\S3\PostObjectV4;
use Aws\S3\S3Client;
use Dotenv\Regex\Success;
use Aws\S3\Exception\S3Exception;

class ContentController extends Controller
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
     *     path="/instructor/content/create",
     *     summary="Create course content",
     *     tags={"Instructor Course Content"},
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
     *         description="Content title",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="section_id",
     *         in="query",
     *         description="Content section id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Content course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Content created successfully",
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
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'string',],
            'section_id' => ['required', 'integer',],
            'course_id' => ['required', 'string',],
        ]);
        Content::create($request->all());
        return $this->success();
    }

    /**
     * @OA\Patch(
     *     path="/instructor/content/update",
     *     summary="Update course content",
     *     tags={"Instructor Course Content"},
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
     *         description="Content id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="section_id",
     *         in="query",
     *         description="Content section id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Content course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Content updated successfully",
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
        Content::whereSectionId($request->section_id)->whereId($request->id)->whereCourseId($request->course_id)->update($request->all());
        return $this->success();
    }


    /**
     * @OA\Delete(
     *     path="/instructor/content/delete/{id}/{section}/{course}",
     *     summary="Delete content",
     *     tags={"Instructor Course Content"},
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
     *         description="content id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="section",
     *         in="query",
     *         description="Content section id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="course",
     *         in="query",
     *         description="Content course id",
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
    public function destroy($id, $section, $course)
    {
        // dd($id, $section, $course);
        $content = Content::whereId($id)->whereSectionId($section)->whereCourseId($course)->first();
        $content->delete();
        return $this->success();
    }


    /**
     * @OA\Post(
     *     path="/instructor/content/store",
     *     summary="Store content File",
     *     tags={"Instructor Course Content"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     * *     @OA\Parameter(
     *         name="courseId",
     *         in="query",
     *         description="course id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="contentId",
     *         in="query",
     *         description="Content id",
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
     *         name="filename",
     *         in="query",
     *         description="Filename",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="directory",
     *         in="query",
     *         description="S3 directory where file will be stored (example, videos,documents, images, avatars)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="oldFile",
     *         in="query",
     *         description="S3 key for previously stored file",
     *         required=false,
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
    public function storeFiles(Request $request)
    {
        try {
            $filename =  $request->filename;
            $directory =  $request->directory;
            $oldKey =  $request->oldFile;
            $id =  $request->id;
            $courseId =  $request->courseId;
            $contentId =  $request->contentId;
            $type = $request->type;
            $duration = $request->duration ?? null;

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


            return $this->storeFilesNow($oldKey, $client, $bucket, $directory, $filename, $id, $courseId, $contentId, $type, $duration);
        } catch (S3Exception $e) {
            return $this->error($e->getAwsErrorMessage());
        }
    }

    public function testStoreFiles(Request $request)
    {

        $oldKey = $request->oldKey;
        $client = $request->client;
        $bucket = $request->bucket;
        $directory = $request->directory;
        $filename = $request->filename;
        $id = $request->id;
        $courseId = $request->courseId;
        $contentId = $request->contentId;
        return $this->storeFilesNow($oldKey, $client, $bucket, $directory, $filename, $id, $courseId, $contentId, false, null);
    }

    public function storeFilesNow($oldKey, $client, $bucket, $directory, $filename, $id, $courseId, $contentId, $type, $duration)
    {
        if ($oldKey && $oldKey !== null) {
            $client->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $oldKey
            ]);
            if ($directory == 'avatars') {
                Account::whereUserId($id)->update([
                    'image_url' => null
                ]);
            }
            if ($directory == 'images') {
                Course::findOrFail($courseId)->update([
                    'image_url' => null
                ]);
            }
            if ($directory == 'videos') {
                $file = CourseFile::whereCourseId($courseId)->whereVideo(true)->whereContentId($contentId)->first();
                $file->delete();
            }
            if ($directory == 'documents') {
                CourseFile::whereCourseId($courseId)->whereDocument(true)->whereContentId($contentId)->first()->delete();
            }
        }
        if ($type == 'upload') {

            return $this->generate($directory, $filename, $client, $bucket, $courseId, $contentId, $duration);
        } else {
            return $this->success();
        }
    }

    public function generate($directory, $filename, $client, $bucket, $courseId, $contentId, $duration)
    {
        $prefix = $directory . '/';
        $acl = 'public-read';
        $expires = '+10 minutes';

        $formInputs = [
            'acl' => $acl,
            'key' => $prefix . $filename,
        ];


        $options = [
            ['acl' => $acl],
            ['bucket' => $bucket],
            ['starts-with', '$key', $prefix],
        ];

        $postObject = new PostObjectV4($client, $bucket, $formInputs, $options, $expires);
        $attributes = $postObject->getFormAttributes();
        $inputs = $postObject->getFormInputs();

        $url = $attributes['action'] . '/' . $directory . '/' . $filename;



        if ($directory == 'videos') {
            CourseFile::create([
                'url' => $url,
                'course_id' => $courseId,
                'content_id' => $contentId,
                'video' => true,
                'duration' => $duration,

            ]);
        }
        if ($directory == 'documents') {
            CourseFile::create([
                'url' => $url,
                'course_id' => $courseId,
                'content_id' => $contentId,
                'document' => true,
            ]);
        }


        return $this->success([
            'attributes' => $attributes,
            'inputs' => $inputs,
            'url' => $url
        ]);
    }
}
