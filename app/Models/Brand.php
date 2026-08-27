<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;

class Brand extends BaseModel
{
    protected $guarded = ['id'];

    protected $logName = "Brand";

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
