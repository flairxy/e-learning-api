<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\CourseFile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{

    public function search(Request $request)
    {
        $query = $request->search;
        $users = User::where('first_name', 'LIKE', '%' . $query . '%')
            ->orWhere('last_name', 'LIKE', '%' . $query . '%')
            ->orWhere('username', 'LIKE', '%' . $query . '%')
            ->get();

        return $this->success($users);
    }

    /**
     * @OA\Get(
     *     path="/admin/users/{role}/approved",
     *     summary="Get Approved Users ",
     *     tags={"Admin Users"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter the users by 'tutor' or 'student'. Default is 'all'",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Approved Users retrieved successfully",
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

    public function approved($role)
    {
        if ($role == 'all') {

            $users = User::whereStatus(User::APPROVED)->whereAdmin(false)->paginate(20);
        } else {

            $users = User::whereStatus(User::APPROVED)->where($role, true)->whereAdmin(false)->paginate(20);
        }
        return $this->paginate($users);
    }

    /**
     * @OA\Get(
     *     path="/admin/users/{role}/pending",
     *     summary="Get Pending Users ",
     *     tags={"Admin Users"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter the users by 'tutor' or 'student'. Default is 'all'",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Pending Users retrieved successfully",
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

    public function pending($role)
    {

        if ($role == 'all') {

            $users = User::whereStatus(User::PENDING)->paginate(20);
        } else {

            $users = User::whereStatus(User::PENDING)->where($role, true)->paginate(20);
        }

        return $this->paginate($users);
    }

    /**
     * @OA\Get(
     *     path="/admin/users/{role}/rejected",
     *     summary="Get Rejected Users ",
     *     tags={"Admin Users"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter the users by 'tutor' or 'student'. Default is 'all'",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Rejected Users retrieved successfully",
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

    public function rejected($role)
    {
        if ($role == 'all') {

            $users = User::whereStatus(User::REJECTED)->paginate(20);
        } else {

            $users = User::whereStatus(User::REJECTED)->where($role, true)->paginate(20);
        }

        return $this->paginate($users);
    }


    /**
     * @OA\Get(
     *     path="/admin/users/{role}/deleted",
     *     summary="Get Deleted Users ",
     *     tags={"Admin Users"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter the users by 'tutor' or 'student'. Default is 'all'",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Deleted Users retrieved successfully",
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
    public function deleted($role)
    {
        if ($role == 'all') {

            $users = User::onlyTrashed()->paginate(20);
        } else {

            $users = User::onlyTrashed()->where($role, true)->paginate(20);
        }

        return $this->paginate($users);
    }

    /**
     * @OA\Patch(
     *     path="/admin/users/revert",
     *     summary="Revert Deleted User",
     *     tags={"Admin Users"},
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
     *         description="User reverted successfully",
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

        User::onlyTrashed()->find($request->id)->restore();
        return $this->success();
    }

    /**
     * @OA\Patch(
     *     path="/admin/users/update",
     *     summary="Update course status",
     *     tags={"Admin Users"},
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
     *         description="User updated successfully",
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

        $user = User::findOrFail($request->id);
        $user->status =  $request->status;
        $user->save();

        return $this->success();
    }


    /**
     * @OA\Delete(
     *     path="/admin/users/delete/{id}/{type}",
     *     summary="Delete a user",
     *     tags={"Admin Users"},
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
     *         description="User id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     * *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Delete type (temp or perm)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User Deleted Successfully",
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
    public function destroy($id, $type)
    {

        $user = User::findOrFail($id);
        if ($type == 'perm') {
            $user->forceDelete();
        } else {
            $user->delete();
        }

        return $this->success();
    }

    public function paginate($data)
    {
        $response = [
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem()
            ],
            'data' => $data
        ];
        return $this->success($response);
    }
}
