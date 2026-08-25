<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base\BaseModel;

class WastageDetail extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "WastageDetail";

    public function getExpiredDateAttribute($value)
    {
        return $value ? date('d M, Y', strtotime($value)) : null;
    }

    public function wastage()
    {
        return $this->belongsTo(Wastage::class, 'wastage_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id', 'id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
