<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grn extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "Grn";

    public function getGrnDateAttribute($value)
    {
        if ($value) {
            return date('d M, Y', strtotime($value));
        }
        return null;
    }

    public function getChallanDateAttribute($value)
    {
        if ($value) {
            return date('d M, Y', strtotime($value));
        }
        return null;
    }

    public function grn_details()
    {
        return $this->hasMany(GrnDetail::class, 'grn_id', 'id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function stock_transactions()
    {
        return $this->hasMany(StockTransaction::class, 'reference_id', 'id')->where('reference_type', 'GRN');
    }

    public static function generateGrnNo()
    {
        $prefix = 'GRN-' . date('Ymd') . '-';
        $lastGrn = self::withTrashed()
            ->where('grn_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastGrn && preg_match('/-(\d+)$/', $lastGrn->grn_no, $matches)) {
            $seq = intval($matches[1]) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
