<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    use HasFactory;

    protected $fillable = ['integration_run_id', 'message', 'is_error', 'is_summary'];

    public function integrationRun()
    {
        return $this->belongsTo(IntegrationRun::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function findByIntegrationRun($tenantId, $integrationRunId, $request)
    {
        $integrationRunId = $request->query('filterByIntegrationRun');
        $summaryOnly = $request->query('filterBySummaryOnly');

        //pagination
        $page = $request->has('_page') ? $request->get('_page') : 1;
        $sort = $request->has('_sort') ? $request->get('_sort') : null;
        $sortDir = $request->has('_sort_dir') ? $request->get('_sort_dir') : 'desc';
        $itemsPerPage = $request->has('_items_per_page') ? $request->get('_items_per_page') : 100000;
        $paginate = $request->has('paginate') ? $request->get('paginate') : true;
        if($paginate === 'false') {
            $paginate = false;
        }

        $query = IntegrationLog::query()
            ->select('integration_logs.*');
        $query->where('integration_logs.integration_run_id', $integrationRunId);
        $query->where('integration_logs.tenant_id', $tenantId);

        if ($summaryOnly) {
            $query->where('integration_logs.is_summary', $summaryOnly);
        }

        if ($sort) {
            $query->orderBy('integration_logs.' . $sort, $sortDir);
        }

        return $paginate ? $query->paginate($itemsPerPage) : $query->get();
    }


}
