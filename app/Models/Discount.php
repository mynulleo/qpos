<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "Discount";

    protected $casts = [
        'discount_value' => 'float',
    ];

    protected $appends = [
        'target_title',
        'discount_display',
        'validity',
        'is_expired'
    ];

    public function getTargetTitleAttribute()
    {
        $scope = $this->scope ?? $this->discount_type;
        if ($scope === 'category') {
            return ($this->category ? $this->category->title : 'N/A') . ' (Category)';
        } elseif ($scope === 'item') {
            $title = $this->item ? $this->item->title : 'N/A';
            if ($this->item && $this->item->barcode) {
                $title .= ' [' . $this->item->barcode . ']';
            }
            return $title;
        }
        return 'All';
    }

    public function getDiscountDisplayAttribute()
    {
        $type = $this->discount_type ?? $this->unit;
        if ($type === 'percentage') {
            return floatval($this->discount_value) . '%';
        }
        return '৳ ' . number_format($this->discount_value, 2);
    }

    public function getValidityAttribute()
    {
        $from = $this->valid_from ? date('d-m-Y', strtotime($this->valid_from)) : '-';
        $to = $this->valid_to ? date('d-m-Y', strtotime($this->valid_to)) : '-';
        return $from . ' to ' . $to;
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->valid_to) return false;
        return strtotime($this->valid_to) < strtotime(date('Y-m-d'));
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'created_by', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'updated_by', 'id');
    }

    /**
     * Scope to fetch only currently active & valid discounts
     */
    public function scopeActiveValid($query, $date = null)
    {
        $today = $date ? date('Y-m-d', strtotime($date)) : date('Y-m-d');
        return $query->where(function ($q) {
                $q->where('status', 1)
                  ->orWhere('status', '1')
                  ->orWhere('status', 'active');
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_from')
                  ->orWhereDate('valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_to')
                  ->orWhereDate('valid_to', '>=', $today);
            });
    }
}
