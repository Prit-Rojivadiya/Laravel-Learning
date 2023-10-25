<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class UserModelHasPermissions extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'model_type', 'model_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function index($tenantId, $userId, $modelType = null, $byType = false, $paginateOptions = null)
    {
        $query = UserModelHasPermissions::query()
            ->select('user_model_has_permissions.*');
        $query->where('user_model_has_permissions.user_id', $userId)
            ->where('user_model_has_permissions.tenant_id', $tenantId)
            ->where('user_model_has_permissions.model_type', $modelType);

        $paginate = false;
        $sort = null;
        $sortDir = 'desc';
        $page = 1;
        $itemsPerPage = 100;

        if ($paginateOptions) {
            $paginate = true;
            if (isset($paginateOptions['sort'])) {
                $sort = $paginateOptions['sort'];
            }
            if (isset($paginateOptions['sortDir'])) {
                $sortDir = $paginateOptions['sortDir'];
            }
            if (isset($paginateOptions['page'])) {
                $page = $paginateOptions['page'];
            }
            if (isset($paginateOptions['itemsPerPage'])) {
                $itemsPerPage = $paginateOptions['itemsPerPage'];
            }
            if ($sort) {
                $query->orderBy('user_model_has_permissions.' . $sort, $sortDir);
            }
        }

        $results = [];
        $items = $paginate ? $query->paginate($itemsPerPage) : $query->get();
        if (!$byType) {
            $results = $items;
        }
        else {
            foreach ($items as $item) {
                if (!array_key_exists($item->model_type, $results)) {
                    $results[$item->model_type] = [];
                }
                array_push($results[$item->model_type], $item->model_id);
            }
        }

        return $results;
    }


}
