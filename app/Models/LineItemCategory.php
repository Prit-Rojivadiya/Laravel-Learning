<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class LineItemCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','code','desc','line_item_type_id','tenant_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lineItemType()
    {
        return $this->belongsTo(LineItemType::class);
    }

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

    public static function findByName($tenantId, $request)
    {
        $name = $request->query('filterByName');
        $lineItemTypeId = $request->query('filterByLineItemType');

        //pagination
        $page = $request->has('_page') ? $request->get('_page') : 1;
        $sort = $request->has('_sort') ? $request->get('_sort') : null;
        $sortDir = $request->has('_sort_dir') ? $request->get('_sort_dir') : 'desc';
        $itemsPerPage = $request->has('_items_per_page') ? $request->get('_items_per_page') : 100000;
        $paginate = $request->has('paginate') ? $request->get('paginate') : true;
        if($paginate === 'false') {
            $paginate = false;
        }

        $query = LineItemCategory::query()
            ->select('line_item_categories.*', 'line_item_types.name as line_item_type_name',DB::raw("concat(line_item_types.name, ' : ' ,line_item_categories.name) AS cat_type_and_name"))
            ->distinct()
            ->join('line_item_types', 'line_item_categories.line_item_type_id', '=', 'line_item_types.id');
        $query->with(['lineItemType']);
        $query->where('line_item_categories.tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(line_item_categories.name) LIKE ?", '%' . $name . '%');
                $q->orWhereRaw("LOWER(line_item_categories.code) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("line_item_categories.id = ?", $name);
                }
            });
        }

        if ($lineItemTypeId) {
            $query->where('line_item_categories.line_item_type_id', $lineItemTypeId);
        }


        if ($sort) {
            if ($sort == 'line_item_type_name') {
                $query->orderBy('line_item_type_name', $sortDir);
            } else {
                $query->orderBy('line_item_categories.' . $sort, $sortDir);
            }
        }
        else {
            $query->orderBy('cat_type_and_name');
        }

        return $paginate ? $query->paginate($itemsPerPage) : $query->get();
    }
}
