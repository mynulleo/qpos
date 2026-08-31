<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "Warehouse";

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function grns()
    {
        return $this->hasMany(Grn::class, 'warehouse_id', 'id');
    }

    public function stock_transactions()
    {
        return $this->hasMany(StockTransaction::class, 'warehouse_id', 'id');
    }
}
