<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;

class ItemStockSummary extends BaseModel
{
    protected $guarded = ['id'];

    protected $logName = "ItemStockSummary";

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
}
