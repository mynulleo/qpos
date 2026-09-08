<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Models\FinancialYear;
use App\Models\Payment;
use App\Models\VoucherDetail;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $logName = "Voucher";

    public static function generateVoucherNo()
    {
        $prefix = 'VOU-' . date('Ymd') . '-';
        $todayVoucher = self::withTrashed()
            ->where('voucherno', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first(['id', 'voucherno']);

        if ($todayVoucher && preg_match('/-(\d+)$/', $todayVoucher->voucherno, $matches)) {
            $seq = intval($matches[1]) + 1;
            return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
        }

        $lastVoucher = self::withTrashed()->orderBy('id', 'desc')->first(['id', 'voucherno']);
        if ($lastVoucher && !empty($lastVoucher->voucherno)) {
            if (preg_match('/^VOU-\d{8}-\d+$/', $lastVoucher->voucherno)) {
                return $prefix . '001';
            } elseif (preg_match('/^(.*?)(\d+)$/', $lastVoucher->voucherno, $matches)) {
                $p = $matches[1];
                $num = intval($matches[2]) + 1;
                return $p . str_pad($num, strlen($matches[2]), '0', STR_PAD_LEFT);
            } elseif (is_numeric($lastVoucher->voucherno)) {
                return (string)(intval($lastVoucher->voucherno) + 1);
            }
        }

        return $prefix . '001';
    }

    public function getVoucherDateAttribute($value)
    {
        $startDate = null;
        if ($value) {
            $startDate = date('d M, Y', strtotime($value));
        }

        return $startDate;
    }

    public function voucher_details()
    {
        return $this->hasMany(VoucherDetail::class, 'voucher_id', 'id')->oldest('id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }

    public function financial_year()
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id', 'id');
    }



    // file image push

    // date format
}
