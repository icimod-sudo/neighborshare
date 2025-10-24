<?php

namespace App\Traits;

trait LogsAdminActivity
{
    /**
     * Log admin action using Spatie Activity Log
     */
    protected function logAdminAction($action, $description, $model = null)
    {
        if (auth()->check() && auth()->user()->is_admin) {
            $activity = activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'action' => $action,
                    'url' => request()->fullUrl()
                ])
                ->log($description);

            // If a model is provided, set it as the subject
            if ($model) {
                $activity->subject()->associate($model);
                $activity->save();
            }

            return $activity;
        }
    }

    /**
     * Log fraud-related actions
     */
    protected function logFraudAction($action, $targetUser, $details = null)
    {
        if (auth()->check() && auth()->user()->is_admin) {
            return activity()
                ->causedBy(auth()->user())
                ->performedOn($targetUser)
                ->withProperties([
                    'ip' => request()->ip(),
                    'action' => $action,
                    'details' => $details,
                    'target_user_id' => $targetUser->id,
                    'target_user_email' => $targetUser->email
                ])
                ->log("Fraud Control: {$action} - {$details}");
        }
    }
}
