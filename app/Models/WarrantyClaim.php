<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Facades\DB;

class WarrantyClaim extends BaseModel
{
    protected $guarded = ['id'];

    protected $logName = "WarrantyClaim";

    public static function generateClaimNo()
    {
        $prefix = 'CLM-' . date('Ymd') . '-';
        $latest = self::where('claim_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = intval(substr($latest->claim_no, strlen($prefix)));
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return $prefix . $nextNumber;
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function invoiceDetail()
    {
        return $this->belongsTo(InvoiceDetails::class, 'invoice_detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function logs()
    {
        return $this->hasMany(WarrantyClaimLog::class, 'warranty_claim_id')->latest();
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
