<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\IntegrationLog;

class IntegrationLogController extends Controller
{
    private const PERMISSION_ENTITY = 'integration_log';

    // GET /integration_logs
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $integrationRunId = $request->has('filterByIntegrationRun') ? $request->get('filterByIntegrationRun') : null;
        if (!$integrationRunId) {
            abort(500, 'Missing Integration run');
        }
        return IntegrationLog::findByIntegrationRun($user->tenant->id, $integrationRunId, $request);
    }

    // GET /integration_logs/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = IntegrationLog::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }

    // POST /integration_logs
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'integration_run_id' => ['required','integer'],
            'message' => ['required', 'string'],
            'is_error' => ['boolean'],
            'is_summary' => ['boolean'],
        ];
        $validated = $request->validate($fields);
        if (!array_key_exists('is_error')) {
            $validated['is_error'] = false;
        }
        if (!array_key_exists('is_summary')) {
            $validated['is_summary'] = false;
        }
        $model = new IntegrationLog($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /integration_logs/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = IntegrationLog::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }

        $fields = [
            'integration_run_id' => ['integer'],
            'message' => ['string'],
            'is_error' => ['boolean'],
            'is_summary' => ['boolean'],
        ];

        if ($model->integration_id != $request->get('integration_id')) {
            $fields['integration_id'] = ['required','integer'];
        }
        if ($model->message != $request->get('message')) {
            $fields['message'] = ['required','string'];
        }
        if ($model->is_error != $request->get('is_error')) {
            $fields['is_error'] = ['required','boolean'];
        }
        if ($model->is_error != $request->get('is_error')) {
            $fields['is_summary'] = ['required','boolean'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);

        return response()->json($model);
    }

    // DELETE /integration_runs/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = IntegrationLog::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }


}
