<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Trait for full audit logging on any model.
 * Add this trait to any model that needs complete activity tracking.
 */
trait HasFullAudit
{
    use LogsActivity;

    /**
     * Get activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        $modelName = class_basename($this);

        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getLogName())
            ->setDescriptionForEvent(function (string $eventName) {
                return $this->getEventDescription($eventName);
            });
    }

    /**
     * Get the log name for this model.
     */
    protected function getLogName(): string
    {
        // Use snake_case of class name as log name
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', class_basename($this))) . 's';
    }

    /**
     * Get description for an event.
     */
    protected function getEventDescription(string $eventName): string
    {
        $modelLabel = $this->getModelLabel();
        $identifier = $this->getModelIdentifier();

        $eventLabels = [
            'created' => 'إنشاء',
            'updated' => 'تحديث',
            'deleted' => 'حذف',
        ];

        $action = $eventLabels[$eventName] ?? $eventName;

        return "تم {$action} {$modelLabel}: {$identifier}";
    }

    /**
     * Get human-readable label for this model.
     * Override in model for custom label.
     */
    protected function getModelLabel(): string
    {
        $labels = [
            'Order' => 'طلب',
            'Product' => 'منتج',
            'Category' => 'قسم',
            'Warehouse' => 'مخزن',
            'StockCount' => 'جلسة جرد',
            'StockMovement' => 'حركة مخزون',
            'Customer' => 'عميل',
            'User' => 'مستخدم',
            'BlogPost' => 'مقال',
            'Coupon' => 'كوبون',
            'Brand' => 'علامة تجارية',
        ];

        return $labels[class_basename($this)] ?? class_basename($this);
    }

    /**
     * Get identifier for this model instance.
     * Override in model for custom identifier.
     */
    protected function getModelIdentifier(): string
    {
        // Try common identifier fields
        if (isset($this->code)) {
            return $this->code;
        }
        if (isset($this->order_number)) {
            return $this->order_number;
        }
        if (isset($this->name)) {
            return $this->name;
        }
        if (isset($this->title)) {
            return $this->title;
        }

        return "#{$this->id}";
    }
}
