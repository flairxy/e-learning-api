<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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
     * @OA\Patch(
     *     path="/user/update",
     *     operationId="/user.update",
     *     summary="Update  user detail",
     *     tags={"Update User"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         description="first Name of the user",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="last_name",
     *         in="query",
     *         description="Last Name of the User",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="other_names",
     *         in="query",
     *         description="Other Name of User",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Email of User",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="Username of the user",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="Phone of the user",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="gender",
     *         in="query",
     *         description="Gender of the User",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="Address of the User",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Update current user details, returns a success response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation Error",
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateUser(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $userNameRegex = usernameRegex();
        $this->validate($request, [
            'first_name' => ['required', 'string', 'min:3'],
            'last_name' => ['required', 'string', 'min:3'],
            'other_names' => ['string', 'min:1'],
            'phone' => ['required', 'string', 'min:9'],
            'email' => ['string', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'username' => ['required', 'string', 'max:255', "regex:$userNameRegex", "unique:users,username,{$user->id}"]
        ]);

        $user->fill($request->all());
        $user->save();

        $account = $user->account;
        $account->update($request->all());

        return $this->success();
    }

    /**
     * @OA\Patch(
     *     path="/user/password",
     *     summary="Update user password",
     *     tags={"Update Password"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Password",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         in="query",
     *         description="Password Confirmation",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Password updated successfully",
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
    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:6|confirmed|max:255',
            'old_password' => 'required|max:255',
        ]);

        $user = $request->user();
        $hashedPassword = $user->password;

        if (Hash::check($request->old_password, $hashedPassword) && !Hash::check($request->password, $hashedPassword)) {
            $user->password = Hash::make($request->password);
            $user->save();
            return $this->success();
        } else {

            return $this->error('Please check the entered credentials');
        }
    }

    /**
     * @OA\Delete(
     *     path="/user/",
     *     summary="Delete a user",
     *     tags={"Delete User"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User Deleted successfully",
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
    public function deleteUser(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->delete();

        return $this->success();
    }

    /**
     * @OA\Get(
     *     path="/user/",
     *     summary="Get current User ",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User retrieved successfully",
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
    public function fetchUser(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->account;
        return $this->success($user);
    }
}
