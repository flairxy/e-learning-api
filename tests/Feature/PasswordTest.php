<?php

namespace Tests\Feature;

use App\Models\Auth\PasswordReset;
use App\Models\Auth\User;
use App\Notifications\ForgotPassword;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use DatabaseMigrations;

    public function testForgotPassword()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();

        $email = $user->email;
        $mail = Notification::fake();
        $mail->assertNothingSent();
        $this->sendPost('password/forgot', ['email' => $email]);
        $this->assertSuccessResponse();
        $mail->hasSent($user, ForgotPassword::class);
    }

    /**
     *
     */
    public function testResetPassword()
    {
        /** @var User $user */
        $user = \factory(User::class)->create();

        $p_reset = PasswordReset::create([
            'email' => $user->email,
            'token' => Str::random(6)
        ]);

        $newPassword = 'secret_pwd';
        //Attempt with wrong token
        $data = [
            'email' => $p_reset->email,
            'token' => 'WrongToken',
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ];
        $this->sendPost('/password/reset', $data);
        $this->assertErrorResponse(Response::HTTP_UNAUTHORIZED);
        //Attempt with valid token
        $data = [
            'email' => $p_reset->email,
            'token' => $p_reset->token,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ];
        $this->sendPost('/password/reset', $data);
        $this->assertSuccessResponse();
        //Validate password was reset
        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

}
