<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentDetail extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $logName = "PaymentDetail";

    // file image push

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'reference_id', 'id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'reference_id', 'id');
    }

    public function grn()
    {
        return $this->belongsTo(Grn::class, 'reference_id', 'id');
    }

    public function sales_return()
    {
        return $this->belongsTo(SalesReturn::class, 'reference_id', 'id');
    }

    public function warranty_claim()
    {
        return $this->belongsTo(WarrantyClaim::class, 'reference_id', 'id');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'reference_id', 'id');
    }

    public function loan_info()
    {
        return $this->belongsTo(LoanInfo::class, 'reference_id', 'id');
    }

    public function salary_sheet()
    {
        return $this->belongsTo(SalarySheet::class, 'reference_id', 'id');
    }

    // date format
}
