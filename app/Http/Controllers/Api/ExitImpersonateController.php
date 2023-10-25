<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTGuard;
use App\Http\Controllers\Controller;

class ExitImpersonateController extends Controller
{
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /** @var JWTGuard $auth */
        $auth = auth('api');

        $oldUserId = $request->session()->get('old_user_id');
        if (empty($oldUserId)) {
            abort(401);
        }

        $user = User::find($oldUserId);

        auth('api')->login($user);

        $request->session()->regenerate();

        return response()->json(['success' => true]);
    }
}
