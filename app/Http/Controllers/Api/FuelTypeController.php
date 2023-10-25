<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuelType;
use Illuminate\Http\Request;

class FuelTypeController extends Controller
{
    private const PERMISSION_ENTITY = 'fueltype';

    // GET /fuel_types
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $itemsPerPage = 100;  //TODO: How do we receive the actual selected items per page.  Not working in SSSF either (see athletes)
        return FuelType::findByName($user->tenant->id, $request->query('filterByName'), $request->query('_sort'), $request->query('_sort_dir'), $itemsPerPage);
    }
}
