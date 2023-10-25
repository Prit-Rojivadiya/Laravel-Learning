<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemMeterType extends Model
{
    use HasFactory;

    protected $fillable = ['name','desc'];

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = SystemMeterType::query()
            ->select('system_meter_types.*');
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("system_meter_types.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('system_meter_types.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }
}
