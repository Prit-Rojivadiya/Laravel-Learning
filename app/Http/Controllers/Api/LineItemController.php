<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LineItem;
use App\Models\RepairOrder;
use App\Models\Tenant;
use Illuminate\Http\Request;

class LineItemController extends Controller
{
    private const PERMISSION_ENTITY = 'lineitem';

    // GET /line_items
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        return LineItem::findByName($user->tenant->id, $request);
    }

    // GET /line_items/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = LineItem::with(['repairOrder','lineItemCategory'])->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }

    // POST /line_items
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'repair_order_id' => ['required','integer'],
            'line_item_category_id' => ['required','integer'],
            'price' => ['required', 'numeric'],
            'quantity' => ['required', 'numeric'],
            'total_price' => ['required', 'numeric'],
        ];
        $validated = $request->validate($fields);

        $repairOrder = RepairOrder::findOrFail($validated['repair_order_id']);
        if (!Tenant::allowedAccess($user->tenant, $repairOrder->tenant)) {
            abort(403, 'Access denied');
        }

        $model = new LineItem($validated);
        $model->tenant_id = $user->tenant->id;
        $model->repair_order_id = $repairOrder->id;
        $model->save();

        $repairOrder->calcTotalPrice();

        return response()->json($model);
    }

    // PUT /line_items/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = LineItem::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }

        $fields = [
            'repair_order_id' => ['integer'],
            'line_item_category_id' => ['integer'],
            'price' => ['numeric'],
            'quantity' => ['numeric'],
            'total_price' => ['numeric'],
        ];

        if ($model->line_item_category_id != $request->get('line_item_category_id')) {
            $fields['line_item_category_id'] = ['required','integer'];
        }

        $repairOrder = RepairOrder::findOrFail($request->get('repair_order_id'));
        if (!Tenant::allowedAccess($user->tenant, $repairOrder->tenant)) {
            abort(403, 'Access denied');
        }
        if ($model->repair_order_id != $request->get('repair_order_id') && !is_null($request->get('repair_order_id'))) {
            $fields['repair_order_id'] = ['required','integer'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);

        $repairOrder->calcTotalPrice();

        return response()->json($model);
    }

    // DELETE /line_items/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = LineItem::findOrFail($id);
        $repairOrder = RepairOrder::findOrFail($model->repair_order_id);
        $model->delete();
        $repairOrder->calcTotalPrice();

        return response()->json(['success' => 1]);
    }

}
