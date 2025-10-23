<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            if (auth()->check()) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'created',
                    'description' => 'Created ' . class_basename($model) . ': ' . $model->getLogDescription(),
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });

        static::updated(function ($model) {
            if (auth()->check()) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => 'Updated ' . class_basename($model) . ': ' . $model->getLogDescription(),
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });

        static::deleted(function ($model) {
            if (auth()->check()) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'deleted',
                    'description' => 'Deleted ' . class_basename($model) . ': ' . $model->getLogDescription(),
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });
    }

    // Default implementation - override in models if needed
    public function getLogDescription()
    {
        if (isset($this->title)) {
            return $this->title;
        } elseif (isset($this->name)) {
            return $this->name;
        } elseif (isset($this->email)) {
            return $this->email;
        }

        return 'ID: ' . $this->id;
    }
}
