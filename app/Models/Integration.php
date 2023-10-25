<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integration extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','client_id','tenant_id','username','password','active'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function integrationGeotab()
    {
        return $this->hasOne(IntegrationGeotab::class);
    }

    public function upsertIntegrationType()
    {
        switch ($this->name) {
            case 'Geotab':
                if (!$this->integrationGeotab()->exists()) {
                    $model = new IntegrationGeotab();
                    $model->tenant_id = $this->tenant_id;
                    $model->integration_id = $this->id;
                    $model->save();
                }
                break;
            default:
                throw new Exception("Missing integration " . $this->name);
                break;
        }
    }

    public static function findByName($tenantId, $clientId, $name, $request)
    {
        //pagination
        $page = $request->has('_page') ? $request->get('_page') : 1;
        $sort = $request->has('_sort') ? $request->get('_sort') : 'name';
        $sortDir = $request->has('_sort_dir') ? $request->get('_sort_dir') : 'asc';
        $itemsPerPage = $request->has('_items_per_page') ? $request->get('_items_per_page') : 100000;
        $paginate = $request->has('paginate') ? $request->get('paginate') : true;
        if($paginate === 'false') {
            $paginate = false;
        }

        $query = Integration::query()
            ->select('integrations.*', 'clients.name as client_name')
            ->join('clients', 'integrations.client_id', '=', 'clients.id');
        $query->with(['client']);
        $query->where('integrations.tenant_id', $tenantId);
        if ($clientId) {
            $query->where('integrations.client_id', $clientId);
        }
        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->whereRaw("LOWER(integrations.name) LIKE ?", '%' . $name . '%');
            });
        }

        if ($sort) {
            $query->orderBy('integrations.' . $sort, $sortDir);
        }

        return $paginate ? $query->paginate($itemsPerPage) : $query->get();
    }


}
