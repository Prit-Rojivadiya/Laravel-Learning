<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','client_id','tenant_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function fleets()
    {
        return $this->hasMany(Fleet::class);
    }

    public static function findByName($tenantId, $name, $clientId = null, $sort = null, $sortDir = null, $itemsPerPage = 100, $modelPermissionsFilter = null)
    {
        $query = Branch::query()
            ->select('branches.*', 'clients.name as client_name')
            ->distinct()
            ->join('clients', 'branches.client_id', '=', 'clients.id');
        $query->with(['client']);
        $query->where('branches.tenant_id', $tenantId);
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(branches.name) LIKE ?", '%' . $name . '%');
                if ((int) $name !== 0) {
                    $q->orWhereRaw("branches.id = ?", $name);
                }
            });
        }
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        if ($modelPermissionsFilter) {
            $query->whereIn('branches.id', $modelPermissionsFilter);
        }

        if ($sort) {
            if ($sort == 'client.name') {
                $query->orderBy('client_name', $sortDir);
            } else {
                $query->orderBy('branches.' . $sort, $sortDir);
            }
        }

        return $query->paginate($itemsPerPage);
    }
}
