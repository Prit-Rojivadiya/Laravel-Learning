<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Vendor extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BelongsToTenant;

    protected $fillable = ['name','abbrv'];

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class);
    }

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = Vendor::query()
            ->select('vendors.*');
        $query->where('tenant_id', $tenantId);

        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(name) LIKE ?", '%' . $name . '%')
                    ->orWhereRaw("LOWER(abbrv) LIKE ?", '%' . $name . '%')
                ;
                if ((int) $name !== 0) {
                    $q->orWhereRaw("vendors.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('vendors.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }
}
