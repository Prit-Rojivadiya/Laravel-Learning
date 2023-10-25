<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\LineItemType;
use App\Models\RepairOrder;
use Illuminate\Http\Request;

class InvoiceSummaryController extends Controller
{
    private const PERMISSION_ENTITY = 'invoice_summary';

    // GET /reports/invoice_summary
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $startDate = $request->has('startDate') ? $request->get('startDate') : null;
        $endDate = $request->has('endDate') ? ($request->get('endDate') . ' 23:59:59') : null;
        $clientId = $request->query('filterByClient');

        //Get repair orders in date range
        $repairOrders = RepairOrder::byDateRange($user->tenant->id, $startDate, $endDate, $clientId);

        $lineItemTypesIndex = [];
        $query = LineItemType::query()
            ->select('line_item_types.*');
        $query->where('tenant_id', $user->tenant->id);
        $lItemTypes = $query->get();
        foreach ($lItemTypes as $key => $lineItemType) {
            $lineItemTypesIndex[$lineItemType->id] = $lineItemType->name;
        }

        foreach ($repairOrders as $key => $repairOrder) {
            $repairOrder = RepairOrder::addLineItemTypeSums($repairOrder, $lineItemTypesIndex);
            $repairOrder->total_price = (float) $repairOrder->total_price;
        }

        $results = [];
        $results['data'] = $repairOrders;
        $results['summaryTypes'] = $lineItemTypesIndex;
        return $results;
    }
}
