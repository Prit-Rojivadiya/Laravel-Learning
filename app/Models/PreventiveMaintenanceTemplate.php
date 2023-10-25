<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreventiveMaintenanceTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','repair_order_type_id','recurring'
    ,'system_meter_type_id','system_p_m_due_type_id'
    ,'length_meters','length_days','desc'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function repairOrderType()
    {
        return $this->belongsTo(RepairOrderType::class);
    }
    public function systemMeterType()
    {
        return $this->belongsTo(SystemMeterType::class);
    }
    public function systemPMDueType()
    {
        return $this->belongsTo(SystemPMDueType::class, 'system_p_m_due_type_id');
    }

    public function preventiveMaintenances()
    {
        return $this->hasMany(PreventiveMaintenance::class);
    }

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = PreventiveMaintenanceTemplate::query()
            ->select('preventive_maintenance_templates.*');
        $query->with(['repairOrderType','systemMeterType','systemPMDueType']);
        $query->where('tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(preventive_maintenance_templates.name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("preventive_maintenance_templates.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('preventive_maintenance_templates.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }
}
