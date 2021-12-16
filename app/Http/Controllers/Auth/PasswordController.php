<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\PasswordReset;
use App\Models\Auth\User;
use App\Notifications\ForgotPassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class PasswordController extends Controller
{
    /**
     * @OA\Post(
     *     path="/password/forgot",
     *     summary="Send token to user's email for verification",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="User's email",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns some sample category things",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Error: Bad request. When required parameters does not exist",
     *     ),
     * )
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetEmail(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        if (is_object($user)) {
            $token = $this->getToken();

            PasswordReset::updateOrCreate(['email' => $email],
                ['token' => $token]);
            $this->sendEmail($user, $token);

            return $this->success();
        } else {
            return $this->error('User does not exit', Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @OA\Post(
     *     path="/forgot/reset",
     *     operationId="/forgot/reset",
     *     summary="Reset user's password using token sent to email",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="User's email Address",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="Token sent to user",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="New password",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         in="query",
     *         description="Password confirmation",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a success response and an API token",
     *         @OA\JsonContent()
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'token' => 'required|string|min:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $p_reset = PasswordReset::where([
            'email' => $request->email,
            'token' => $request->token
        ])
            ->where('updated_at', '>', Carbon::now()->addHours(-1))
            ->exists();
        if ($p_reset) {
            $new_password = Hash::make($request->password);
            $user = User::whereEmail($request->email)->first();
            if (is_object($user)) {
                $user->password = $new_password;
                $user->save();

                return $this->success([
                    'token' => $user->api_token
                ]);
            } else {
                return $this->error('Email does not exist');
            }
        } else {
            return $this->error('Wrong email or expired token', Response::HTTP_UNAUTHORIZED);
        }

    }

    private function getToken()
    {
        return Str::random(6);
    }

    private function sendEmail(User $user, $token)
    {
        $user->notify(new ForgotPassword($token));
    }
}
