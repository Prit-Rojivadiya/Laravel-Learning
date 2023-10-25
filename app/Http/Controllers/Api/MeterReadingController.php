<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Vehicle;
use App\Models\MeterReading;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    private const PERMISSION_ENTITY = 'meter_reading';
    private const PERMISSION_FLEET = 'fleet';

    // GET /meter_readings
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fleetPermissionsFilter = $user->getModelPermissionsFilter(self::PERMISSION_FLEET);
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        return MeterReading::findByName($user->tenant->id, $request->query('filterByName'), $request->query('filterByVehicle'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage, $fleetPermissionsFilter);
    }

    // GET /meter_readings/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = MeterReading::with('vehicle')->findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->vehicle->fleet->id)) {
            abort(401, 'Unauthorized');
        }
        return response()->json($model);
    }


    // POST /meter_readings
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            'vehicle_id' => ['required','integer'],
            'meter_reading' => ['required','integer'],
            'meter_reading_date' => ['required','date'],
            'notes' => ['string'],
            'source_id' => ['integer'],
            'source' => ['string'],
            'external_source' => ['string'],
            'external_id' => ['string'],
            'location_country' => ['string'],
            'location_state' => ['string'],
        ];
        $validated = $request->validate($fields);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
            abort(403, 'Access denied');
        }

        $model = new MeterReading($validated);
        $model->tenant_id = $user->tenant->id;
        $model->vehicle_id = $vehicle->id;
        $model->save();

        return response()->json($model);
    }

    // PUT /meter_readings/{id}
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = MeterReading::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }
        if (!$user->hasPermission(self::PERMISSION_FLEET, __FUNCTION__, $model->vehicle->fleet->id)) {
            abort(401, 'Unauthorized');
        }

        $fields = [
            'vehicle_id' => ['integer'],
            'meter_reading' => ['integer'],
            'meter_reading_date' => ['date','nullable'],
            'notes' => ['string','nullable'],
            'source_id' => ['integer','nullable'],
            'source' => ['string','nullable'],
            'external_source' => ['string','nullable'],
            'external_id' => ['string','nullable'],
            'location_country' => ['string','nullable'],
            'location_state' => ['string','nullable'],
        ];
        if ($model->vehicle_id != $request->get('vehicle_id') && !is_null($request->get('vehicle_id'))) {
            $vehicle = Vehicle::findOrFail($request->get('vehicle_id'));
            if (!Tenant::allowedAccess($user->tenant, $vehicle->tenant)) {
                abort(403, 'Access denied');
            }
            $fields['vehicle_id'] = ['required','integer'];
        }
        if ($model->meter_reading_date != $request->get('meter_reading_date')) {
            $fields['meter_reading_date'] = ['required','date'];
        }
        if ($model->source_id != $request->get('source_id')) {
            $fields['source_id'] = ['required','integer'];
        }
        if ($model->source != $request->get('source')) {
            $fields['source'] = ['required','string'];
        }
        if ($model->external_source != $request->get('external_source')) {
            $fields['external_source'] = ['required','string'];
        }
        if ($model->external_id != $request->get('external_id')) {
            $fields['external_id'] = ['required','string'];
        }
        if ($model->location_country != $request->get('location_country')) {
            $fields['location_country'] = ['required','string'];
        }
        if ($model->location_state != $request->get('location_state')) {
            $fields['location_state'] = ['required','string'];
        }

        $fieldsToUpdate = $request->validate($fields);
        $model->update($fieldsToUpdate);
        return response()->json($model);
    }

    // DELETE /meter_readings/{id}
    public function delete($id)
    {
        //TODO: how to check permissions on the user?
        $model = MeterReading::findOrFail($id);
        $model->delete();
        return response()->json(['success' => 1]);
    }
}
