<?php

/**
 * @Quill Information Technology
 */

namespace App\Models\System;

use App\Models\Base\BaseModel;
use App\Models\Currency;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends BaseModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'show_pos_terms' => 'integer',
        'show_invoice_terms' => 'integer',
        'show_pos_item_popup' => 'integer',
        'memberships' => 'array',
        'coupon_enabled' => 'integer',
        'default_vat' => 'float',
    ];

    public function setShowPosItemPopupAttribute($value)
    {
        $this->attributes['show_pos_item_popup'] = ($value === 'false' || $value === false || $value === '0' || $value === 0 || empty($value)) ? 0 : 1;
    }

    public function setShowPosTermsAttribute($value)
    {
        $this->attributes['show_pos_terms'] = ($value === 'false' || $value === false || $value === '0' || $value === 0 || empty($value)) ? 0 : 1;
    }

    public function setShowInvoiceTermsAttribute($value)
    {
        $this->attributes['show_invoice_terms'] = ($value === 'false' || $value === false || $value === '0' || $value === 0 || empty($value)) ? 0 : 1;
    }

    protected $logName = 'Site Settings';
    protected $appends = ['original_logo', 'logo_one', 'logo_two', 'logo_three', 'original_logo_small', 'logo_small_one', 'logo_small_two', 'logo_small_three'];

    public function getMembershipsAttribute($value)
    {
        if (empty($value)) return [];
        if (is_array($value)) {
            if (count($value) === 1 && is_string($value[0]) && (str_starts_with(trim($value[0]), '[') || str_starts_with(trim($value[0]), '{'))) {
                $sub = json_decode($value[0], true);
                if (is_array($sub)) return $sub;
            }
            return $value;
        }
        $decoded = json_decode($value, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        return is_array($decoded) ? $decoded : [];
    }
    public function getOriginalLogoAttribute()
    {
        return getJsonMediaUrl($this->logo, 'original');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_id', 'id');
    }

    public function getOriginalLogoSmallAttribute()
    {
        return getJsonMediaUrl($this->logo_small, 'original');
    }

    public function getLogoOneAttribute()
    {
        return getJsonMediaUrl($this->logo, 0);
    }

    public function getLogoTwoAttribute()
    {
        return getJsonMediaUrl($this->logo, 1);
    }

    public function getLogoThreeAttribute()
    {
        return getJsonMediaUrl($this->logo, 2);
    }

    public function getLogoSmallOneAttribute()
    {
        return getJsonMediaUrl($this->logo_small, 0);
    }

    public function getLogoSmallTwoAttribute()
    {
        return getJsonMediaUrl($this->logo_small, 1);
    }

    public function getLogoSmallThreeAttribute()
    {
        return getJsonMediaUrl($this->logo_small, 2);
    }

    public function getFaviconAttribute($value)
    {
        return !empty($value) && Storage::exists($value)
            ? Storage::url($value)
            : asset('images/favicon.png');
    }
}
