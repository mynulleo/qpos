<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermsCondition extends BaseModel
{
    use SoftDeletes;

    protected $table = 'terms_conditions';
    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
        'sorting' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module_name', $module);
    }
}
