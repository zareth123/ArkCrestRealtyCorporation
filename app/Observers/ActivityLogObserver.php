<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic, centralized activity/audit observer.
 *
 * Attached to models (see ActivityLogServiceProvider) rather than written into
 * individual controllers. Every Create / Update / Delete (and Restore) on an
 * observed model is captured automatically, including a field-level
 * before/after diff, the acting user, and the module the record belongs to.
 */
class ActivityLogObserver
{
    /**
     * Attributes that are never included in a logged diff/snapshot for ANY model —
     * either because they are not meaningful to an audit trail (timestamps, ids)
     * or because they are sensitive (passwords, tokens).
     */
    protected static array $globalExcludedFields = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'password',
        'remember_token',
        'security_answer',
        'security_question',
        'email_verification_code',
        'api_token',
        'last_login_at',
        'last_seen_at',
        'email_verified_at',
    ];

    /**
     * Maps a model's FQCN to the human-readable "Module" name shown in Edit History.
     * Extend this list as new modules/models are added to the system.
     */
    public static array $moduleMap = [
        \App\Models\Client::class                 => 'Client Database',
        \App\Models\ReservedClient::class          => 'Client Database',
        \App\Models\PersonnelContact::class        => 'Client Database',
        \App\Models\TripSchedule::class            => 'Site Visit / Tripping',
        \App\Models\CommissionRequest::class       => 'Commission Monitoring',
        \App\Models\CommissionRequestSales::class  => 'Commission Monitoring',
        \App\Models\DownpaymentInstallment::class  => 'Commission Monitoring',
        \App\Models\ArkcrestCommissionRate::class  => 'Commission Monitoring',
        \App\Models\DepartmentalExpense::class     => 'Departmental Expenses',
        \App\Models\Expense::class                 => 'Departmental Expenses',
        \App\Models\ExpenseCategory::class         => 'Departmental Expenses',
        \App\Models\HrForm::class                  => 'HR',
        \App\Models\Note::class                    => 'Notes',
        \App\Models\PermissionRequest::class       => 'Permission Requests',
        \App\Models\SummaryReport::class           => 'Summary Reports',
        \App\Models\Department::class              => 'Settings',
        \App\Models\SalesTeam::class               => 'Sales & Marketing',
        \App\Models\SalesAgent::class               => 'Sales & Marketing',
        \App\Models\TeamMonthlyQuota::class         => 'Sales & Marketing',
        \App\Models\Property::class                 => 'Settings',
        \App\Models\PeriodLock::class                => 'Settings',
        \App\Models\User::class                      => 'Settings',
    ];

    public function created(Model $model): void
    {
        $this->record('create', $model, $this->snapshot($model, 'new'));
    }

    public function updated(Model $model): void
    {
        $changes = $this->diff($model);
        if (empty($changes)) {
            return; // nothing meaningful changed (e.g. only an excluded/timestamp field)
        }
        $this->record('update', $model, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record('delete', $model, $this->snapshot($model, 'old'));
    }

    public function restored(Model $model): void
    {
        $this->record('restore', $model, $this->snapshot($model, 'new'));
    }

    /* ------------------------------------------------------------------ */

    protected function record(string $action, Model $model, array $changes): void
    {
        // Avoid noisy logs from seeders/artisan commands (not real user actions).
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        $module = static::$moduleMap[get_class($model)] ?? $this->modelName($model);
        $label  = $this->labelFor($model);
        $verb   = ucfirst($action) . 'd'; // create->Created, update->Updated, delete->Deleted, restore->Restored

        $description = "{$verb} " . $this->modelName($model)
            . ($label ? " '{$label}'" : '')
            . " (ID: {$model->getKey()})";

        try {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'module'      => $module,
                'description' => $description,
                'ip'          => request()->ip(),
                'meta'        => [
                    'record_type'  => $this->modelName($model),
                    'model_class'  => get_class($model),
                    'record_id'    => $model->getKey(),
                    'record_label' => $label,
                    'changes'      => $changes,
                ],
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the actual user action.
            report($e);
        }
    }

    /** Field-level before/after diff for an update. */
    protected function diff(Model $model): array
    {
        $changes = [];
        foreach ($model->getChanges() as $key => $new) {
            if (in_array($key, static::$globalExcludedFields, true)) continue;
            $old = $model->getOriginal($key);
            if ($old === $new) continue;
            $changes[$key] = [
                'old' => $this->stringify($old),
                'new' => $this->stringify($new),
            ];
        }
        return $changes;
    }

    /** Full-record snapshot for create/delete/restore, shaped like a diff (one side null). */
    protected function snapshot(Model $model, string $side): array
    {
        $changes = [];
        foreach ($model->getAttributes() as $key => $value) {
            if (in_array($key, static::$globalExcludedFields, true)) continue;
            $changes[$key] = $side === 'new'
                ? ['old' => null, 'new' => $this->stringify($value)]
                : ['old' => $this->stringify($value), 'new' => null];
        }
        return $changes;
    }

    protected function stringify(mixed $value): mixed
    {
        // Preserve real types for bool/int/float so a later restore/revert can write
        // them straight back into the database without lossy string coercion
        // (e.g. MySQL treats BOTH "true" and "false" as 0 in a numeric column).
        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }
        if (is_array($value)) $value = json_encode($value);
        $value = (string) $value;
        return mb_strlen($value) > 300 ? mb_substr($value, 0, 300) . '…' : $value;
    }

    /** Picks the most human-friendly identifying label available on the record. */
    protected function labelFor(Model $model): ?string
    {
        foreach ([
            'client_name', 'name', 'title', 'control_number', 'team_name',
            'category', 'project_name', 'email', 'control_no',
        ] as $field) {
            $val = $model->getAttribute($field);
            if (!empty($val)) return (string) $val;
        }
        return null;
    }

    /** "CommissionRequestSales" -> "Commission Request Sales" */
    protected function modelName(Model $model): string
    {
        $base = class_basename($model);
        return trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $base));
    }
}