<?php

namespace App\Models;

use App\Helpers\GlobalHelper;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;

class Agent extends BaseModel
{
    use SoftDeletes;

    protected $table = 'agents';

    protected $guarded = ['id'];

    protected $logName = "Agent";

    protected $appends = ['is_delete', 'original_image', 'image_one', 'image_two', 'image_three'];

    public function getDescriptionForEvent(string $eventName): string
    {
        $guard = GlobalHelper::get_guard();
        $name = Auth::guard($guard)->user()->name ?? '';

        return "{$name} - {$eventName} this";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty(true)
            ->logUnguarded(true)
            ->logOnly(['*'])
            ->useLogName($this->logName);
    }

    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (!is_string($value)) {
            return json_encode($value);
        }

        if (is_base64($value)) {
            return $value;
        }

        if (str_contains($value, '[') || str_contains($value, '{')) {
            return $value;
        }

        if (!str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
            return url("") . "/public/storage/" . $value;
        }

        return $value;
    }

    public function getOriginalImageAttribute()
    {
        $raw = $this->getRawOriginal('image');
        if (empty($raw)) {
            return null;
        }
        if (!str_contains($raw, '[') && !str_contains($raw, '{')) {
            return $this->image;
        }
        return getJsonMediaUrl($raw, 'original');
    }

    public function getImageOneAttribute()
    {
        $raw = $this->getRawOriginal('image');
        if (empty($raw)) {
            return null;
        }
        if (!str_contains($raw, '[') && !str_contains($raw, '{')) {
            return $this->image;
        }
        return getJsonMediaUrl($raw, 0);
    }

    public function getImageTwoAttribute()
    {
        $raw = $this->getRawOriginal('image');
        if (empty($raw)) {
            return null;
        }
        if (!str_contains($raw, '[') && !str_contains($raw, '{')) {
            return $this->image;
        }
        return getJsonMediaUrl($raw, 1);
    }

    public function getImageThreeAttribute()
    {
        $raw = $this->getRawOriginal('image');
        if (empty($raw)) {
            return null;
        }
        if (!str_contains($raw, '[') && !str_contains($raw, '{')) {
            return $this->image;
        }
        return getJsonMediaUrl($raw, 2);
    }

    public function getIsDeleteAttribute()
    {
        return $this->deleted_at !== null;
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'agent_id', 'id');
    }
}
