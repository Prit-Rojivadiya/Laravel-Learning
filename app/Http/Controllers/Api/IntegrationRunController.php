<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\IntegrationRun;

class IntegrationRunController extends Controller
{
    private const PERMISSION_ENTITY = 'integration_run';

    // GET /integration_runs
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        return IntegrationRun::findByIntegrationTask($user->tenant->id, $request);
    }

    // GET /integration_runs/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = IntegrationRun::with(['integration'])->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }

    // POST /integration_runs
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'integration_id' => ['required','integer'],
            'task' => ['required', 'string'],
            'started' => ['datetime'],
            'completed' => ['completed'],
            'total' => ['integer'],
            'success_count' => ['integer'],
            'failed_count' => ['integer'],
            'results' => ['array'],
        ];
        $validated = $request->validate($fields);
        $model = new IntegrationRun($validated);
        $model->tenant_id = $user->tenant->id;
        if (!$model->started) {
            $model->started = new \DateTime();
        }
        $model->save();

        return response()->json($model);
    }

    // PUT /integration_runs/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = IntegrationRun::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }

        $fields = [
            'integration_id' => ['integer'],
            'task' => ['string'],
            'started' => ['datetime'],
            'completed' => ['completed'],
            'total' => ['integer'],
            'success_count' => ['integer'],
            'failed_count' => ['integer'],
            'results' => ['array'],
        ];

        if ($model->integration_id != $request->get('integration_id')) {
            $fields['integration_id'] = ['required','integer'];
        }
        if ($model->task != $request->get('task')) {
            $fields['task'] = ['required','string'];
        }
        if ($model->started != $request->get('started')) {
            $fields['started'] = ['required','datetime'];
        }

        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);

        return response()->json($model);
    }

    // DELETE /integration_runs/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = IntegrationRun::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }


}
