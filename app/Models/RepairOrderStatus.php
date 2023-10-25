<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairOrderStatus extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','desc','tenant_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class);
    }

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = RepairOrderStatus::query()
            ->select('repair_order_statuses.*');
        $query->where('repair_order_statuses.tenant_id', $tenantId);

        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("repair_order_statuses.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('repair_order_statuses.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }


}
