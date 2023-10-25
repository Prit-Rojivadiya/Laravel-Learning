<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            return (new MailMessage)
                ->subject(__('Reset Password Notification'))
                ->line(__('You are receiving this email because we received a password reset request for your account.'))
                ->action(__('Reset Password'), config('app.nuxt_url').'auth/reset-password?token='.$token)
                ->line(__('If you did not request a password reset, no further action is required.'));
        });
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json(['status' => trans($response)]);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        if ($response == Password::INVALID_USER) {
            return response()->json(['status' => trans(Password::RESET_LINK_SENT)]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($response)],
        ]);
    }

    /**
     * Validate the email for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        $request->validate(['email' => 'required']);
    }
}
