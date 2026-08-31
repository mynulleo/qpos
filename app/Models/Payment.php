<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base\BaseModel;

class Payment extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "Payment";

    public static function getPaySlipNo()
    {
        $slipno = 111;
        $payment = Payment::latest()->first(['id', 'payslipno']);

        if ($payment && $payment->payslipno) {
            if (is_numeric($payment->payslipno)) {
                $slipno = intval($payment->payslipno) + 1;
            } elseif (preg_match('/(\d+)$/', $payment->payslipno, $matches)) {
                $num = intval($matches[1]) + 1;
                $prefix = preg_replace('/\d+$/', '', $payment->payslipno);
                $slipno = $prefix . str_pad($num, strlen($matches[1]), '0', STR_PAD_LEFT);
            } else {
                $slipno = (intval($payment->id) + 111);
            }
        }
        return $slipno;
    }

    public function getPaymentDateAttribute($value)
    {
        $startDate = null;
        if ($value) {
            $startDate = date('d M, Y', strtotime($value));
        }

        return $startDate;
    }

    public function payment_details()
    {
        return $this->hasMany(PaymentDetail::class, 'payment_id', 'id')->oldest('id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }

    // file image push

    // date format
}
