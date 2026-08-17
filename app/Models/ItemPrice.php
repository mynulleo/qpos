<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;

class ItemPrice extends BaseModel
{
    protected $guarded = ['id'];
    
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
}
