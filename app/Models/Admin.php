<?php

namespace App\Models;

use App\Helpers\GlobalHelper;
use App\Models\System\Role;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Admin extends Authenticatable
{
    use Notifiable, SoftDeletes, LogsActivity;

    protected $connection = 'accessdb';
    protected $table = 'organization_users';

    protected $guarded = ['id'];

    protected $logName = "Admin";

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['name', 'is_delete', 'original_profile', 'profile_one', 'profile_two', 'profile_three'];

    private static bool $isResolvingScope = false;

    public static function getActiveOrganizationId(): ?int
    {
        if (static::$isResolvingScope) {
            return null;
        }

        static::$isResolvingScope = true;

        $orgId = null;
        try {
            if (Auth::guard('admin')->hasUser()) {
                $orgId = Auth::guard('admin')->user()->organization_id ?? null;
            } elseif (Auth::hasUser()) {
                $orgId = Auth::user()->organization_id ?? null;
            } elseif (session()->has('organization_id')) {
                $orgId = session('organization_id');
            }
        } catch (\Throwable $e) {
            $orgId = null;
        } finally {
            static::$isResolvingScope = false;
        }

        return $orgId ? (int) $orgId : null;
    }

    protected static function booted()
    {
        static::addGlobalScope('organization', function ($builder) {
            $orgId = static::getActiveOrganizationId();
            if (!empty($orgId)) {
                $builder->where($builder->getModel()->getTable() . '.organization_id', $orgId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->organization_id)) {
                $orgId = static::getActiveOrganizationId();
                if (!empty($orgId)) {
                    $model->organization_id = $orgId;
                }
            }
        });
    }

    public function getNameAttribute()
    {
        return $this->attributes['full_name'] ?? ($this->attributes['name'] ?? '');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id')->select('id', 'name');
    }

    public function getRoleAttribute()
    {
        if ($this->relationLoaded('role')) {
            $r = $this->getRelation('role');
            if ($r) return $r;
        }

        if (!empty($this->role_id)) {
            try {
                $conn = config('database.default', 'mysql');
                return Role::on($conn)->select('id', 'name')->find($this->role_id);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        $guard = GlobalHelper::get_guard();
        $name = '';
        if (Auth::guard($guard)->hasUser()) {
            $name = Auth::guard($guard)->user()->name ?? '';
        }

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

    public function getProfileAttribute($value)
    {
        if (!is_string($value)) {
            return json_encode($value, true);
        }

        return $value;
    }

    public function getOriginalProfileAttribute()
    {
        return getJsonMediaUrl($this->profile, 'original');
    }

    public function getProfileOneAttribute()
    {
        return getJsonMediaUrl($this->profile, 0);
    }
    public function getProfileTwoAttribute()
    {
        return getJsonMediaUrl($this->profile, 1);
    }
    public function getProfileThreeAttribute()
    {
        return getJsonMediaUrl($this->profile, 2);
    }

    public function getIsDeleteAttribute()
    {
        if ($this->deleted_at != null) {
            return true;
        }
        return false;
    }

    public function getBirthDateAttribute($value)
    {
        return $value ? date('d M, Y', strtotime($value)) : null;
    }
}
