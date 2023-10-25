<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Tenant;
use Illuminate\Http\Request;

class VehicleMeterReadingController extends Controller
{
    private const PERMISSION_ENTITY = 'vehicle_meter_reading';

    // GET /vehicle_meter_reading/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $model = Vehicle::findOrFail($id);
        if (!Tenant::allowedAccess($user->tenant, $model->tenant)) {
            abort(403, 'Access denied');
        }

        if ($request->query('latest')) {
            $results = $model->latestMeterReading();
        }
        else {
            $results = $model->meterReadings();
        }
        return response()->json($results);
    }
}
