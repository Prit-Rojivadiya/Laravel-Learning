<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\IntegrationGeotab;
use App\Models\IntegrationRun;
use App\Models\Tenant;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function PHPUnit\Framework\throwException;

class IntegrationController extends Controller
{
    private const PERMISSION_ENTITY = 'integration';

    // GET /integrations
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant->id;
        $clientId = $request->has('filterByClient') ? $request->get('filterByClient') : null;
        $name = $request->has('name') ? $request->get('name') : null;

        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        return Integration::findByName($tenantId, $clientId, $name, $request);
    }

    // GET /integrations/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = Integration::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        return response()->json($model);
    }

    // POST /integrations
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $uniqueTenantRule = Rule::unique('integrations')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });
        $fields = [
            'client_id' => ['required','integer'],
            'name' => ['required',$uniqueTenantRule,'string'],
            'active' => ['required', 'boolean'],
            'username' => ['string'],
            'password' => ['string'],
        ];
        $validated = $request->validate($fields);

        $model = new Integration($validated);
        $model->tenant_id = $user->tenant->id;
        $model->save();
        $model->upsertIntegrationType();

        return response()->json($model);
    }

    // PUT /integrations/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = Integration::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        $uniqueTenantRule = Rule::unique('integrations')->where(function ($query) use ($user) {
            return $query->where('tenant_id', $user->tenant->id);
        });

        $fields = [
            'client_id' => ['integer'],
            'name' => ['string'],
            'active' => ['boolean'],
            'username' => ['string'],
            'password' => ['string'],
        ];

        if ($model->client_id != $request->get('client_id')) {
            $fields['client_id'] = ['required','integer'];
        }
        if ($model->name != $request->get('name') && !is_null($request->get('name'))) {
            $fields['name'] = [$uniqueTenantRule, 'string'];
        }
        if ($model->active != $request->get('active')) {
            $fields['active'] = ['required','boolean'];
        }
        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        $model->upsertIntegrationType();

        return response()->json($model);
    }

    // DELETE /integrations/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = Integration::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }

    // PUT /integrations/run/{id}
    public function run(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, 'update')) {
            abort(401, 'Unauthorized');
        }
        $integration = Integration::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $integration->tenant)) {
            abort(403, 'Access denied');
        }

        $fields = [
            'task' => ['required','string'],
            'startDate' => ['string'],
            'endDate' => ['string'],
            'dateTimeZone' => ['string']
        ];
        $reqData = $request->validate($fields);
        $task = $reqData['task'];

        $results = null;
        $integrationRun = new IntegrationRun();
        $integrationRun->integration_id = $integration->id;
        $integrationRun->tenant_id = $integration->tenant_id;
        $integrationRun->task = $task;
        $integrationRun->started = new DateTime();
        $integrationRun->status = 'running';
        $integrationRun->total = 0;
        $integrationRun->success_count = 0;
        $integrationRun->failed_count = 0;
        $integrationRun->save();
        try {
            $integrationRun->logMessage("Starting " . $integration->name . " " . $task, false, true);
            switch ($integration->name) {
                case 'Geotab':
                    $geotab = IntegrationGeotab::findByIntegration($integration->id);
                    $taskResults = $geotab->runTask($task, $integrationRun, $reqData);
                    $results = $taskResults;
                    break;
                default:
                    throw new Exception("Missing integration " . $integration->name);
                    break;
            }
        }
        catch (Exception $e) {
            $integrationRun->logMessage("Failed to run " . $task . " with error: ". $e->getMessage(), true);
            $results['error'] = $e->getMessage();
            //abort(500, $results['error']);
        }

        $integrationRun->refresh();
        $integrationRun->finished($results);
        return response()->json($integrationRun->refresh());
    }



}
