<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTGuard;
use App\Http\Controllers\Controller;

class ImpersonateController extends Controller
{
    /**
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $id)
    {
        if (!$request->user()->hasCachedPermissionTo('impersonate')) {
            abort(401);
        }

        $oldUserId = $request->user()->getKey();

        /** @var JWTGuard $auth */
        $auth = auth('api');

        $user = User::find($id);
        auth('api')->login($user);

//        $request->session()->regenerate();
        $request->session()->put('old_user_id', $oldUserId);

        return response()->json(['success' => true]);
    }
}
