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

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }

    public function fund_account()
    {
        return $this->belongsTo(Account::class, 'fund_account_id', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }

    public function sales_return()
    {
        return $this->hasOne(SalesReturn::class, 'payment_id', 'id');
    }

    public function warranty_claim()
    {
        return $this->hasOne(WarrantyClaim::class, 'payment_id', 'id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'payment_id', 'id');
    }

    protected $appends = ['can_edit', 'can_delete', 'lock_reason', 'invoice_numbers'];

    public function getInvoiceNumbersAttribute()
    {
        $numbers = [];
        if ($this->relationLoaded('payment_details')) {
            foreach ($this->payment_details as $detail) {
                if ($detail->reference_type === 'Invoice' && $detail->relationLoaded('invoice') && $detail->invoice) {
                    $numbers[] = $detail->invoice->invoice_no;
                } elseif ($detail->reference_type === 'Purchase' && $detail->relationLoaded('purchase') && $detail->purchase) {
                    $numbers[] = $detail->purchase->invoiceno;
                }
            }
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('invoices', 'payment_id') && $this->relationLoaded('invoices') && $this->invoices) {
            foreach ($this->invoices as $inv) {
                if (!empty($inv->invoice_no)) {
                    $numbers[] = $inv->invoice_no;
                }
            }
        }
        $numbers = array_values(array_unique(array_filter($numbers)));
        return !empty($numbers) ? implode(', ', $numbers) : null;
    }

    public function getCanEditAttribute()
    {
        return !$this->isSystemLocked();
    }

    public function getCanDeleteAttribute()
    {
        return !$this->isSystemLocked();
    }

    public function getLockReasonAttribute()
    {
        if ($this->hasSalesReturn()) {
            $srNo = ($this->relationLoaded('sales_return') && $this->sales_return) ? $this->sales_return->return_no : null;
            if (!$srNo) {
                $sr = SalesReturn::where('payment_id', $this->id)->first();
                if ($sr) {
                    $srNo = $sr->return_no;
                }
            }
            $ref = $srNo ? " (#{$srNo})" : "";
            return "This payment is linked to Sales Return{$ref}. For accounting and stock consistency, it cannot be edited or deleted directly. Please contact the developer if needed.";
        }
        if ($this->hasWarrantyClaim()) {
            $clmNo = ($this->relationLoaded('warranty_claim') && $this->warranty_claim) ? $this->warranty_claim->claim_no : null;
            if (!$clmNo) {
                $claim = WarrantyClaim::where('payment_id', $this->id)->first();
                if ($claim) {
                    $clmNo = $claim->claim_no;
                }
            }
            $ref = $clmNo ? " (#{$clmNo})" : "";
            return "This payment is linked to Warranty Claim{$ref}. For accounting integrity, it cannot be edited or deleted directly. Please contact the developer if needed.";
        }
        return null;
    }

    public function hasSalesReturn(): bool
    {
        if ($this->relationLoaded('sales_return') && $this->sales_return) {
            return true;
        }
        if ($this->relationLoaded('payment_details')) {
            if ($this->payment_details->contains('reference_type', 'SalesReturn')) {
                return true;
            }
        }
        return $this->payment_details()->where('reference_type', 'SalesReturn')->exists()
            || SalesReturn::where('payment_id', $this->id)->exists();
    }

    public function hasWarrantyClaim(): bool
    {
        if ($this->relationLoaded('warranty_claim') && $this->warranty_claim) {
            return true;
        }
        if ($this->relationLoaded('payment_details')) {
            if ($this->payment_details->contains('reference_type', 'WarrantyClaim')) {
                return true;
            }
        }
        return $this->payment_details()->where('reference_type', 'WarrantyClaim')->exists()
            || WarrantyClaim::where('payment_id', $this->id)->exists();
    }

    public function isSystemLocked(): bool
    {
        return $this->hasSalesReturn() || $this->hasWarrantyClaim();
    }

    // file image push

    // date format
}

