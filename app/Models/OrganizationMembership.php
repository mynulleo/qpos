<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class OrganizationMembership extends BaseModel
{
    use SoftDeletes;

    protected $table = 'organization_memberships';
    protected $guarded = ['id'];

    protected $appends = ['logo_url'];

    protected $casts = [
        'show_in_invoice' => 'boolean',
        'sorting' => 'integer',
    ];

    public function getLogoUrlAttribute()
    {
        if (empty($this->logo)) {
            return null;
        }

        if (str_starts_with($this->logo, 'http') || str_starts_with($this->logo, 'data:image')) {
            return $this->logo;
        }

        if (Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }

        return asset($this->logo);
    }
}
