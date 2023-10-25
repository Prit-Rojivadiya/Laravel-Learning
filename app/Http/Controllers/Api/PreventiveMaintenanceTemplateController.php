<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenanceTemplate;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreventiveMaintenanceTemplateController extends Controller
{
    private const PERMISSION_ENTITY = 'preventive_maintenance_template';

    // GET /preventive_maintenance_templates
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        return PreventiveMaintenanceTemplate::findByName($user->tenant->id, $request->query('filterByName'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage);
    }

    // GET /preventive_maintenance_templates/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = PreventiveMaintenanceTemplate::with(['repairOrderType','systemMeterType','systemPMDueType'])->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }

    // POST /preventive_maintenance_templates
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $uniqueTenantRule = Rule::unique('preventive_maintenance_templates')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['required',$uniqueTenantRule,'string'],
            'repair_order_type_id' => ['required','integer'],
            'system_meter_type_id' => ['required','integer'],
            'system_p_m_due_type_id' => ['required','integer'],
            'recurring' => ['required','boolean'],
            'length_meters' => ['required','integer'],
            'length_days' => ['required','integer'],
            'desc' => ['string'],
        ];
        $validated = $request->validate($fields);

        $model = new PreventiveMaintenanceTemplate($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /preventive_maintenance_templates/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = PreventiveMaintenanceTemplate::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        $uniqueTenantRule = Rule::unique('preventive_maintenance_templates')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'name' => ['string'],
            'recurring' => ['boolean'],
            'length_meters' => ['integer'],
            'length_days' => ['integer'],
            'desc' => ['string'],
        ];

        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->repair_order_type_id != $request->get('repair_order_type_id')) {
            $fields['repair_order_type_id'] = ['required','integer'];
        }
        if ($model->system_meter_type_id != $request->get('system_meter_type_id')) {
            $fields['system_meter_type_id'] = ['required','integer'];
        }
        if ($model->system_p_m_due_type_id != $request->get('system_p_m_due_type_id')) {
            $fields['system_p_m_due_type_id'] = ['required','integer'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /preventive_maintenance_templates/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = PreventiveMaintenanceTemplate::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
