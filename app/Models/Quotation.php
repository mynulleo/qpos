<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base\BaseModel;

class Quotation extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "Quotation";

    /**
     * Generate auto unique Quotation Number.
     * Format: QT-YYMM-0001
     */
    public static function generateQuotationNo()
    {
        $prefix = 'QT-' . date('ym') . '-';
        $latest = self::withTrashed()
            ->where('quotation_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest && !empty($latest->quotation_no)) {
            $lastNumber = intval(substr($latest->quotation_no, strlen($prefix)));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function preparedBy()
    {
        return $this->belongsTo(Employee::class, 'prepared_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'id');
    }

    public function quotation_details()
    {
        return $this->hasMany(QuotationDetail::class, 'quotation_id', 'id')->orderBy('sorting', 'asc')->orderBy('id', 'asc');
    }

    public function details()
    {
        return $this->hasMany(QuotationDetail::class, 'quotation_id', 'id')->orderBy('sorting', 'asc')->orderBy('id', 'asc');
    }
}
