<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationRun extends Model
{
    use HasFactory;

    protected $fillable = ['integration_id', 'task', 'started', 'completed',
        'total', 'success_count', 'failed_count', 'status', 'summary_msg', 'error_msg', 'results'];

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public function integrationLog()
    {
        return $this->hasMany(IntegrationLog::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function findByIntegrationTask($tenantId, $request)
    {
        $integrationId = $request->query('filterByIntegration');
        $task = $request->query('filterByTask');

        //pagination
        $page = $request->has('_page') ? $request->get('_page') : 1;
        $sort = $request->has('_sort') ? $request->get('_sort') : 'started';
        $sortDir = $request->has('_sort_dir') ? $request->get('_sort_dir') : 'desc';
        $itemsPerPage = $request->has('_items_per_page') ? $request->get('_items_per_page') : 100000;
        $paginate = $request->has('paginate') ? $request->get('paginate') : true;
        if($paginate === 'false') {
            $paginate = false;
        }

        $query = IntegrationRun::query()
            ->select('integration_runs.*');
        // $query->with(['integrationLog']); //not necesssary and causees index views to take too long to load
        $query->where('integration_runs.integration_id', $integrationId);
        $query->where('integration_runs.tenant_id', $tenantId);

        if ($task) {
            $query->where('integration_runs.task', $task);
        }

        if ($sort) {
            $query->orderBy('integration_runs.' . $sort, $sortDir);
        }

        return $paginate ? $query->paginate($itemsPerPage) : $query->get();
    }

    public function setTotal($total)
    {
        $this->total = $total;
        $this->save();
    }

    public function setCounts($total = 0, $success = 0, $failed = 0)
    {
        $this->total = $total;
        $this->success_count = $success;
        $this->failed_count = $failed;
        $this->save();
    }

    public function logMessage($message, $isError = false, $isSummary = false)
    {
        $logMsg = new IntegrationLog();
        $logMsg->tenant_id = $this->tenant_id;
        $logMsg->integration_run_id = $this->id;
        $logMsg->message = $message;
        $logMsg->is_error = $isError;
        $logMsg->is_summary = $isSummary;
        $logMsg->save();
    }

    public function successPlus($plus = 1)
    {
        $this->success_count += $plus;
        $this->save();
    }

    public function failedPlus($plus = 1)
    {
        $this->failed_count += $plus;
        $this->save();
    }

    public function finished($results)
    {
        $this->completed = new DateTime();
        $this->results = $results;
        $failed = false;
        if( ($this->failed_count > 0) || (isset($results['error'])) || (isset($results['failed'])) ) {
            $failed = true;
        }
        if (isset($results['error'])) {
            $this->error_msg = $results['error'];
        }
        if (isset($results['failed'])) {
            if (!isset($this->error_msg)) {
                $this->error_msg = "";
            }
            foreach ($results['failed'] as $key => $value) {
                $this->error_msg = ($this->error_msg . ", " . $key . ": " . $value);
            }
            if (substr($this->error_msg, 0, 2) === ", ") {
                $this->error_msg = substr($this->error_msg, 2);
            }
        }

        if ($failed) {
            $this->status = 'failed';
            $this->summary_msg = "Task failed to run successfully";
            $this->logMessage($this->summary_msg, true, true);
        }
        else {
            $this->status = 'successful';
            if (!isset($integrationRun->summary_msg)) {
                $this->summary_msg = 'Task completed successfully';
                $this->logMessage($this->summary_msg, false, true);
            }
        }

        $maxstrlength = 255 - 3; //postgress string max lenth + '...'
        if (strlen($this->error_msg) > $maxstrlength) {
            $this->error_msg = substr($this->error_msg, 0, $maxstrlength) . '...';
        }
        $this->save();
    }



}
