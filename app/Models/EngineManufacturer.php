<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngineManufacturer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = EngineManufacturer::query()
            ->select('engine_manufacturers.*');
        $query->where('tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("engine_manufacturers.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('engine_manufacturers.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }
}
