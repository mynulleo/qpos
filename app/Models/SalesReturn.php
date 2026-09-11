<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturn extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $logName = "SalesReturn";

    /**
     * Generate unique Return Number (e.g. RET-20260911-0001)
     */
    public static function generateReturnNo()
    {
        $today = date('Ymd');
        $prefix = "RET-{$today}-";
        $latest = self::withTrashed()
            ->where('return_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest && preg_match('/-(\d+)$/', $latest->return_no, $matches)) {
            $seq = intval($matches[1]) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }

    public function wastage()
    {
        return $this->belongsTo(Wastage::class, 'wastage_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'id');
    }

    public function details()
    {
        return $this->hasMany(SalesReturnDetail::class, 'sales_return_id', 'id');
    }
}
