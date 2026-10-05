<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Traits\VoucherTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base\BaseModel;

class Invoice extends BaseModel
{
    use VoucherTrait, SoftDeletes;

    protected $guarded = ['id'];
    protected $logName = "Invoice";

    protected $casts = [
        'terms_conditions' => 'array',
    ];

    public function getTermsConditionsAttribute($value)
    {
        if (empty($value)) return [];
        if (is_array($value)) {
            // Check if array has a single double-encoded string
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
        return is_array($decoded) ? $decoded : [];
    }

    protected static function booted()
    {
        static::created(function ($invoice) {
            $vdata = [
                'module'    => 'Invoice',
                'date'      => vue_to_server_date($invoice->invoice_date),
                'amount'    => $invoice->amount,
                'source_id' => $invoice->id,
                'ref_id'    => $invoice->client_id,
            ];

            app()->make(self::class)->createReceivableVoucher($vdata);
        });

        // 🔹 After update → delete old voucher & recreate
        static::updated(function ($invoice) {

            // 1️⃣ Remove previous voucher
            $source = [
                'source'    => 'Invoice',
                'source_id' => $invoice->id,
            ];

            $invoice->removeVoucherBySourceInfo($source);

            // 2️⃣ Re-create voucher
            $vdata = [
                'module'    => 'Invoice',
                'date'      => vue_to_server_date($invoice->invoice_date),
                'amount'    => $invoice->amount,
                'source_id' => $invoice->id,
                'ref_id'    => $invoice->client_id,
            ];

            $invoice->createReceivableVoucher($vdata);
        });
    }

    // file image push
    public static function generateInvoiceNumber()
    {
        $siteSetting = \App\Models\System\SiteSetting::first();
        $prefix = !empty($siteSetting->invoice_prefix) ? trim($siteSetting->invoice_prefix) : 'POS';
        $prefix = rtrim($prefix, '-');

        $lastInvoice = self::orderBy('id', 'desc')->first();
        $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
        return $prefix . '-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
    // date format

    public function getInvoiceDateAttribute($value)
    {
        $startDate = null;
        if ($value) {
            $startDate = date('d M, Y', strtotime($value));
        }

        return $startDate;
    }

    public function invoice_details()
    {
        return $this->hasMany(InvoiceDetails::class, 'invoice_id', 'id')->oldest('id');
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetails::class, 'invoice_id', 'id')->oldest('id');
    }

    public function invoice_months()
    {
        return $this->hasMany(InvoiceMonth::class, 'invoice_id', 'id')->oldest('id');
    }


    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payment_details()
    {
        return $this->hasMany(PaymentDetail::class, 'reference_id', 'id')
            ->where('reference_type', 'Invoice')
            ->oldest('id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'id');
    }
}
