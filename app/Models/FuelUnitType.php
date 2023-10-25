<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelUnitType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','desc','tenant_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = FuelUnitType::query()
            ->select('fuel_unit_types.*');
        $query->where('tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("fuel_unit_types.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('fuel_unit_types.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }
}
