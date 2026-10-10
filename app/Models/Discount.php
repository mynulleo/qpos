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
        'items'          => 'array',
    ];

    protected $appends = [
        'name',
        'unit',
        'description',
        'target_title',
        'discount_display',
        'validity',
        'is_expired'
    ];

    public function getNameAttribute()
    {
        return $this->attributes['title'] ?? null;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['title'] = $value;
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['notes'] ?? null;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['notes'] = $value;
    }

    public function getUnitAttribute()
    {
        return $this->attributes['discount_type'] ?? 'percentage';
    }

    public function setUnitAttribute($value)
    {
        $this->attributes['discount_type'] = $value;
    }

    public function getItemIdAttribute()
    {
        if (!empty($this->items) && is_array($this->items)) {
            return $this->items[0] ?? null;
        }
        return null;
    }

    public function getStatusAttribute($value)
    {
        return ($value === 'active' || $value == 1 || $value === '1') ? 'active' : 'deactive';
    }

    public function setStatusAttribute($value)
    {
        if ($value === 'deactive' || $value === 'inactive' || $value === 0 || $value === '0' || $value === false) {
            $this->attributes['status'] = 'deactive';
        } else {
            $this->attributes['status'] = 'active';
        }
    }

    public function setDiscountTypeAttribute($value)
    {
        if (in_array($value, ['category', 'item'])) {
            $this->attributes['scope'] = $value;
        } else {
            $this->attributes['discount_type'] = $value;
        }
    }

    public function getTargetTitleAttribute()
    {
        $scope = $this->scope ?? $this->discount_type;
        if ($scope === 'category') {
            return ($this->category ? $this->category->title : 'N/A') . ' (Category)';
        } elseif ($scope === 'item') {
            $itemIds = is_array($this->items) ? $this->items : [];
            $count = count($itemIds);
            if ($count === 0) {
                return 'N/A (Item)';
            }
            if ($count === 1) {
                $item = Item::find($itemIds[0]);
                if ($item) {
                    return $item->title . ($item->barcode ? ' [' . $item->barcode . ']' : '');
                }
                return "Item #{$itemIds[0]}";
            }
            return "{$count} Items Selected";
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
