<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemPMDueType extends Model
{
    use HasFactory;

    protected $fillable = ['name','desc'];

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = SystemPMDueType::query()
            ->select('system_p_m_due_types.*');
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("system_p_m_due_types.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('system_p_m_due_types.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }
}
