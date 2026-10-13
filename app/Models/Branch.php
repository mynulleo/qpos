<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;

class Branch extends BaseModel
{
    protected $guarded = ['id'];

    protected $logName = "Branch";

    protected $appends = ['name', 'title'];

    public function getNameAttribute()
    {
        return $this->attributes['branch_name'] ?? '';
    }

    public function getTitleAttribute()
    {
        return $this->attributes['branch_name'] ?? '';
    }

    // file image push
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    // date format
}
