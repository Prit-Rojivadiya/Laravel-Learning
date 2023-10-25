<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairOrderType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','code','desc','tenant_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }


    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = RepairOrderType::query()
            ->select('repair_order_types.*');
        $query->where('tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(repair_order_types.name) LIKE ?", '%' . $name . '%');
                $q->orWhereRaw("LOWER(repair_order_types.code) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("repair_order_types.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('repair_order_types.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }
}
