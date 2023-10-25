<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // PUT /api/profile
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $model = User::findOrFail($id);
        if ($id != $user->id) {
            abort(403, 'Unauthorized update');
        }
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }

        $fields = [];

        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = ['required','string'];
        }
        if ($model->email != $request->get('email') && !is_null($request->get('email'))) {
            $fields['email'] = ['unique:users', 'string','email'];
            $validator = Validator::make($request->all(), [
                'email' => [
                    'required',
                    function($attribute, $value, $fail) use($request) {
                        $duplicateEmailCount = User::whereRaw('LOWER(email) = ?', strtolower($request->get('email')))->count();
                        if ($duplicateEmailCount > 0) {
                            $fail('The email "'.$value.'"" has already been taken. Please enter a different email');
                        }
                    }
                ],
            ]);
            $validator->validate();
        }

        $fieldsToUpdate = $request->validate($fields);

        if (isset($fieldsToUpdate['email'])) {
            $fieldsToUpdate['email'] = strtolower($fieldsToUpdate['email']);
        }

        if (sizeof($fieldsToUpdate) > 0) {
            $model->update($fieldsToUpdate);
        }
        return response()->json(['success' => false]);
    }

}
