<?php

namespace App\Observers;

use App\Models\Employee;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    /**
     * Handle the Media "created" event.
     *
     * @param  \App\Models\Spatie\MediaLibrary\MediaCollections\Models\Media  $media
     * @return void
     */
    public function created(Media $media)
    {
        //
    }

    /**
     * Handle the Media "updated" event.
     *
     * @param  \App\Models\Spatie\MediaLibrary\MediaCollections\Models\Media  $media
     * @return void
     */
    public function updated(Media $media)
    {
        //
    }

    /**
     * Handle the Media "deleted" event.
     *
     * @param  \App\Models\Spatie\MediaLibrary\MediaCollections\Models\Media  $media
     * @return void
     */
    public function deleted(Media $media)
    {
        if ($media->model_type == Employee::class && $media->collection_name == 'employee_photo') {
            $employee = $media->model;
            if (!$employee->hasMedia('employee_photo') && ($employee->hasMedia('employee_photo') || $employee->clearMediaCollection('identity_card'))) {
                $employee->clearMediaCollection('passport_photo');
                $employee->clearMediaCollection('identity_card');
            }
        }
    }

    /**
     * Handle the Media "restored" event.
     *
     * @param  \App\Models\Spatie\MediaLibrary\MediaCollections\Models\Media  $media
     * @return void
     */
    public function restored(Media $media)
    {
        //
    }

    /**
     * Handle the Media "force deleted" event.
     *
     * @param  \App\Models\Spatie\MediaLibrary\MediaCollections\Models\Media  $media
     * @return void
     */
    public function forceDeleted(Media $media)
    {
        //
    }
}
