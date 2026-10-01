<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base\BaseModel;

class Purchase extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $logName = "Purchase";
    protected $appends = ['can_edit', 'can_delete'];

    public function getCanEditAttribute()
    {
        if (isset($this->attributes['receive_status']) && in_array($this->attributes['receive_status'], ['Partial', 'Received'])) {
            return false;
        }
        if (isset($this->grns_count) && $this->grns_count > 0) {
            return false;
        }
        if ($this->relationLoaded('grns') && $this->grns && $this->grns->count() > 0) {
            return false;
        }
        return true;
    }

    public function getCanDeleteAttribute()
    {
        return $this->getCanEditAttribute();
    }

    public function getPurchaseDateAttribute($value)
    {
        $startDate = null;
        if ($value) {
            $startDate = date('d M, Y', strtotime($value));
        }

        return $startDate;
    }

    public function purchase_details()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }


    public function grns()
    {
        return $this->hasMany(Grn::class, 'purchase_id', 'id');
    }

    public static function generateInvoiceNo()
    {
        $prefix = 'PUR-' . date('Ymd') . '-';
        $latest = self::withTrashed()
            ->where('invoiceno', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest && !empty($latest->invoiceno) && preg_match('/-(\d+)$/', $latest->invoiceno, $matches)) {
            $seq = intval($matches[1]) + 1;
        } else {
            $countToday = self::withTrashed()
                ->where('invoiceno', 'like', "{$prefix}%")
                ->count();
            $seq = $countToday + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
