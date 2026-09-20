<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;

class Series extends BaseModel
{
    protected $guarded = ['id'];

    protected $logName = "Series";

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'series_id', 'id');
    }
}
