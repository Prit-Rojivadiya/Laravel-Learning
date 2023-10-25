<?php

namespace App\Http\Controllers\Auth;

use App\Auth\TranzITProvider;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UpdatePasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function update(Request $request)
    {
        $requireCurrentPassword = true;
        $user = $request->user();
        if ($request->has('userId') && $request->get('userId') != $request->user()->id && $request->user()->hasCachedPermissionTo('manage any user')) {
            $requireCurrentPassword = false;
            $user = User::find($request->get('userId'));
        }
        $request->validate($this->rules($user, $requireCurrentPassword));
        $this->resetPassword($user, $request->post('new_password'));
        return response()->json(['success' => true]);
    }

    protected function rules(User $user, $requireCurrentPassword = true)
    {
        $currentPasswordRule = ['required', function ($attribute, $value, $fail) use ($user) {
            $provider = new TranzITProvider(app('hash'), User::class);

            if (!$provider->validateCredentials($user, ['password' => $value])) {
                return $fail(__('The current password is incorrect.'));
            }
        }];
        $newPasswordRule = [
            'required',
            'confirmed',
            //'min:8',             // must be at least 8 characters in length
            //'regex:/[a-z]/',      // must contain at least one lowercase letter
            //'regex:/[A-Z]/',      // must contain at least one uppercase letter
            //'regex:/[0-9]/',      // must contain at least one digit
            //'regex:/[@$!%*#?&]/', // must contain a special character
        ];
//            Password::mixedCase()
//                ->mixedCase()
//                ->letters()
//                ->numbers()
//                ->symbols()
//                ->uncompromised(),

        $rules = null;
        if (!$requireCurrentPassword) {
            $rules = [
                'new_password' => $newPasswordRule
            ];
        }
        else {
            $rules = [
                'current_password' => $currentPasswordRule,
                'new_password' => $newPasswordRule
            ];
        }
        return $rules;
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'new_password', 'new_password_confirmation'
        );
    }
}
