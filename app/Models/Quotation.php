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

    protected $casts = [
        'terms_conditions' => 'array',
    ];

    public function getTermsConditionsAttribute($value)
    {
        if (empty($value)) return [];
        if (is_array($value)) {
            if (count($value) === 1 && is_string($value[0]) && (str_starts_with(trim($value[0]), '[') || str_starts_with(trim($value[0]), '{'))) {
                $sub = json_decode($value[0], true);
                if (is_array($sub)) return $sub;
            }
            return $value;
        }
        $decoded = json_decode($value, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        if (is_array($decoded)) {
            return $decoded;
        }
        if (is_string($value) && trim($value) !== '') {
            return array_values(array_filter(array_map('trim', explode("\n", $value))));
        }
        return [];
    }

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
