<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LineItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['repair_order_id', 'line_item_category_id', 'price',
        'quantity', 'total_price'];

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function lineItemCategory()
    {
        return $this->belongsTo(LineItemCategory::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function findByName($tenantId, $request)
    {
        $repairOrderId = $request->query('filterByRepairOrder');

        //pagination
        $page = $request->has('_page') ? $request->get('_page') : 1;
        $sort = $request->has('_sort') ? $request->get('_sort') : null;
        $sortDir = $request->has('_sort_dir') ? $request->get('_sort_dir') : 'desc';
        $itemsPerPage = $request->has('_items_per_page') ? $request->get('_items_per_page') : 100000;
        $paginate = $request->has('paginate') ? $request->get('paginate') : true;
        if($paginate === 'false') {
            $paginate = false;
        }

        $query = LineItem::query()
            ->select('line_items.*');
        $query->with(['repairOrder','lineItemCategory']);
        $query->where('line_items.tenant_id', $tenantId);

        if ($repairOrderId) {
            $query->where('line_items.repair_order_id', $repairOrderId);
        }

        if ($sort) {
            $query->orderBy('line_items.' . $sort, $sortDir);
        }

        return $paginate ? $query->paginate($itemsPerPage) : $query->get();
    }

}
