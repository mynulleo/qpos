<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base\BaseModel;

class StockAdjustment extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "StockAdjustment";

    /**
     * Generate auto unique Adjustment Number.
     * Format: ADJ-YYYYMMDD-001
     */
    public static function generateAdjustmentNo()
    {
        $prefix = 'ADJ-' . date('Ymd') . '-';
        $latest = self::withTrashed()
            ->where('adjustment_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest && !empty($latest->adjustment_no)) {
            $lastNumber = intval(substr($latest->adjustment_no, strlen($prefix)));
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return $prefix . $nextNumber;
    }

    public function getAdjustmentDateAttribute($value)
    {
        return $value ? date('d M, Y', strtotime($value)) : null;
    }

    public function adjustment_details()
    {
        return $this->hasMany(StockAdjustmentDetail::class, 'stock_adjustment_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(StockAdjustmentDetail::class, 'stock_adjustment_id', 'id');
    }

    public function conductedBy()
    {
        return $this->belongsTo(Employee::class, 'conducted_by', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
}
