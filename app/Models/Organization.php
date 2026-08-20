<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;

class Organization extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    protected $connection = 'accessdb'; // 👈 এখানে আলাদা ডাটাবেজ কানেকশন
    protected $table = 'organizations';       // 👈 যদি টেবিলের নাম আলাদা হয়, সেটাও দিতে হবে

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty(true)
            ->logUnguarded(true)
            ->logOnly(['*'])
            ->useLogName($this->logName);
    }

    // file image push
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public static function updateOrganizationExpireDate($organization_id, $month_duration)
    {
        $organization = self::find($organization_id);

        if ($organization) {
            $today = new \DateTime();
            $currentExpireDate = $organization->expired_date
                ? new \DateTime($organization->expired_date)
                : new \DateTime();

            // If already expired in the past, renew starting from today
            if ($currentExpireDate < $today) {
                $currentExpireDate = new \DateTime();
            }

            // Add selected month duration
            $currentExpireDate->modify("+{$month_duration} months");

            $organization->expired_date = $currentExpireDate->format('Y-m-d');
            $organization->save();

            return $organization->expired_date;
        }

        return false;
    }
}
