<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Auth\User;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/login",
     *     operationId="/login",
     *     summary="Login a user and obtain token",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="email_or_username",
     *         in="query",
     *         description="The login parameters should be in a post request, email or username",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Some optional other parameter",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a success response and an API token",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Error: Bad request. When the merchant does not exist.",
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request. When the merchant name(account_id) is not a word or mis-spelt.",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Error: Bad request. When the user does not exist.",
     *     ),
     * )
     *
     * Handles login
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email_or_username' => 'required|string|max:255',
            'password' => 'required|string',
        ]);
        $email_or_username = $request->input('email_or_username');
        $user = User::where(function ($builder) use ($email_or_username) {
            $builder->where('username', $email_or_username)->orWhere('email', $email_or_username);
        })->first();
        abort_unless(is_object($user), Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        $verify = Hash::check($request->password, $user->password);
        abort_unless($verify, Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        /** @var User $user */
        $user->api_token = $this->createToken($user->id);
        $user->account;
        $user->save();

        return $this->success([
            'token' => $user->api_token,
            'user' => $user,
            //            'nearby' => $user->role,
            //            'rewards' => $user->role,
        ]);
    }


    /**
     * @OA\Post(
     *     path="/register",
     *     operationId="/register/user",
     *     summary="Registers a user",
     *     tags={"Auth"},
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
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="Phone of the user",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *         name="terms",
     *         in="query",
     *         description="Terms and condition ",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Creates a new user, returns a success response with an API token",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation Error",
     *     ),
     * )
     *
     * Handles Registration
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function registerUser(Request $request)
    {
        $userNameRegex = usernameRegex();
        $this->validate($request, [
            'first_name' => ['required', 'string', 'min:3'],
            'last_name' => ['required', 'string', 'min:3'],
            'other_names' => ['string', 'min:1'],
            'phone' => ['required', 'string', 'min:9'],
            'email' => ['string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', "regex:$userNameRegex", 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        return DB::transaction(function () use ($request) {
            // Create User
            $user = new User($request->all());
            $user->password = Hash::make($request->password);
            $user->api_token = $this->createToken();
            $require_email = Setting::get('REQUIRE_EMAIL');
            $manual_approval = Setting::get('APPROVE_ACCOUNTS');
            $email_extensions = Setting::get('AUTO_APPROVE_EMAIL_EXT');

            if ($require_email) {
                $this->validate($request, [
                    'email' => ['required', 'email', 'max:255', 'unique:users'],
                ]);
            }

            if (!$manual_approval) {
                $user->status = 2;
            }

            if ($email_extensions) {
                $email_domain =  explode("@", $request->email);
                $approved_email = in_array($email_domain[1], $email_extensions, true);
                if ($approved_email) {
                    $user->status = 2;
                }
            }

            $request->tutor ? $user->tutor = true : $user->student = true;
            $user->save();

            $account = new Account();
            $account->user_id = $user->id;
            $account->save();

            $user->account = $account;

            return $this->success([
                'token' => $user->api_token,
                'user' => $user,
                //            'nearby' => $user->role,
                //            'rewards' => $user->role,
            ]);
        });
    }

    /**
     * @param int|null $user_id
     * @return string
     * @throws \Exception
     */
    private function createToken($user_id = null)
    {
        return sha1($user_id . bin2hex(random_bytes(16)));
    }


    /**
     * @OA\Post(
     *     path="/logout",
     *     operationId="/logout",
     *     summary="Destroys user token",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response="200",
     *         description="Returns a success response and an API token",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function logout(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->api_token = null;
        $user->save();
        return $this->success();
    }

    public function settings()
    {
        $settings = Setting::all();
        $key = env('PAYSTACK_PUBLIC');
        return $this->success(['settings' => $settings, 'key' => $key]);
    }
}
