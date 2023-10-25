<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LineItemType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','code','desc','tenant_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lineItems()
    {
        return $this->hasMany(LineItemCategory::class);
    }

    public static function findByName($tenantId, $name, $sort = null, $sortDir = null, $itemsPerPage = 100)
    {
        $query = LineItemType::query()
            ->select('line_item_types.*');
        $query->where('tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(line_item_types.name) LIKE ?", '%' . $name . '%');
                $q->orWhereRaw("LOWER(line_item_types.code) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("line_item_types.id = ?", $name);
                }
            });
        }

        if ($sort) {
            $query->orderBy('line_item_types.' . $sort, $sortDir);
        }

        return $query->paginate($itemsPerPage);
    }
}
