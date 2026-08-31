<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrnDetail extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "GrnDetail";

    public function grn()
    {
        return $this->belongsTo(Grn::class, 'grn_id', 'id');
    }

    public function purchase_detail()
    {
        return $this->belongsTo(PurchaseDetail::class, 'purchase_detail_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id', 'id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id', 'id');
    }
}
