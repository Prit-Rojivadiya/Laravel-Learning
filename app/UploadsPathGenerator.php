<?php

namespace App;

use App\Models\RepairOrder;
use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class UploadsPathGenerator implements PathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        $base = $this->getBasePath($media).'/'.$media->collection_name.'/';
        if ($media->model instanceof RepairOrder && $media->collection_name == 'invoices') {
            /** @var RepairOrder $repairOrder */
            $repairOrder = $media->model;
            $base .= $repairOrder->vehicle->fleet->branch->name.'/Week Ending '.(Carbon::now()->endOfWeek()->format('m.d.Y')).'/'.$media->getKey().'/';;
        } else {
            // each file must be stored within it's own directory due to upload library
            $base .= $media->getKey().'/';
        }

        return $base;
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $prefix = config('media-library.prefix', '');

        if ($prefix !== '') {
            return $prefix . '/' . $media->getKey();
        }

        $media->model;
        if ($media->model instanceof RepairOrder) {
            $tenant = $media->model->tenant;

            return $tenant->getKey().'-'.$tenant->abbrv.'-'.$tenant->name;
        }

        return $media->getKey();
    }
}
