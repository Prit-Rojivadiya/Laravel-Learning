<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LineItemCategory;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LineItemCategoryController extends Controller
{
    private const PERMISSION_ENTITY = 'lineitem_category';

    // GET /line_item_categories
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        return LineItemCategory::findByName($user->tenant->id, $request);
    }

    // GET /line_item_categories/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = LineItemCategory::with('lineItemType')->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }

    // POST /line_item_categories
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $uniqueTenantRule = Rule::unique('line_item_categories')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['required',$uniqueTenantRule,'string'],
            'code' => [$uniqueTenantRule,'string','nullable'],
            'desc' => ['string','nullable'],
            'line_item_type_id' => ['required','integer'],
        ];
        $validated = $request->validate($fields);

        $model = new LineItemCategory($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /line_item_categories/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = LineItemCategory::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        $uniqueTenantRule = Rule::unique('line_item_categories')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['string'],
            'code' => ['string','nullable'],
            'desc' => ['string','nullable'],
            'line_item_type_id' => ['integer'],
        ];

        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->code != $request->get('code') && !is_null($request->get('code'))) {
            $fields['code'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->line_item_type_id != $request->get('line_item_type_id')) {
            $fields['line_item_type_id'] = ['required','integer'];
        }

        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /line_item_categories/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = LineItemCategory::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
