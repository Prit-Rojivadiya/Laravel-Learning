<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UploadsController extends Controller
{
    private const PERMISSION_ENTITY = 'tenant';

    // POST /upload
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission('repair_order', 'store')) {
            abort(401, 'Unauthorized');
        }

        /** @var RepairOrder $model */
        $model = RepairOrder::findOrFail($request->post('repair_order_id'));
        $model
            ->addMediaFromRequest('file')
            ->toMediaCollection($request->post('file_type'));
    }

    // DELETE /uploads/{id}
    public function delete(Request $request, $id)
    {
        $user = $request->user();
        $model = Media::findOrFail($id);

        $parentModel = $model->model;
        if ($parentModel instanceof RepairOrder && $user->hasPermission('repair_order', 'store')) {
            $model->delete();

            return response()->json(['success' => true]);
        }

        abort(401, 'Unauthorized');
    }
}
