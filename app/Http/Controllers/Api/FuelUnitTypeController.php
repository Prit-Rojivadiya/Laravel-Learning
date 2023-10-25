<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuelUnitType;
use Illuminate\Http\Request;

class FuelUnitTypeController extends Controller
{
    private const PERMISSION_ENTITY = 'fuelunit_type';

    // GET /fuel_unit_types
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        return FuelUnitType::findByName($user->tenant->id, $request->query('filterByName'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage);
    }
}
