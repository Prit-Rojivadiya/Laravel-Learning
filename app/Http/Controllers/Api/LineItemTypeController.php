<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LineItemType;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LineItemTypeController extends Controller
{
    private const PERMISSION_ENTITY = 'lineitem_type';

    // GET /line_item_types
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        return LineItemType::findByName($user->tenant->id, $request->query('filterByName'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage);
    }

    // GET /line_item_types/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = LineItemType::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }

    // POST /line_item_types
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $uniqueTenantRule = Rule::unique('line_item_types')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['required',$uniqueTenantRule,'string'],
            'code' => ['required',$uniqueTenantRule,'string'],
            'desc' => ['required','string'],
        ];
        $validated = $request->validate($fields);

        $model = new LineItemType($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /line_item_types/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = LineItemType::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        $uniqueTenantRule = Rule::unique('line_item_types')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['string'],
            'code' => ['string'],
            'desc' => ['string'],
        ];

        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->code != $request->get('code') && !is_null($request->get('code'))) {
            $fields['code'] = [$uniqueTenantRule, 'string'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /line_item_types/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = LineItemType::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
