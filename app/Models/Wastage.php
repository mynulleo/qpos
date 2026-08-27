<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base\BaseModel;

class Wastage extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "Wastage";

    /**
     * Generate auto unique Audit Number.
     * Format: WST-YYYYMMDD-001
     */
    public static function generateAuditNumber()
    {
        $prefix = 'WST-' . date('Ymd') . '-';
        $latest = self::withTrashed()
            ->where('audit_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest && !empty($latest->audit_number)) {
            $lastNumber = intval(substr($latest->audit_number, strlen($prefix)));
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return $prefix . $nextNumber;
    }

    public function getAuditDateAttribute($value)
    {
        return $value ? date('d M, Y', strtotime($value)) : null;
    }

    public function getApprovedDateAttribute($value)
    {
        return $value ? date('d M, Y h:i A', strtotime($value)) : null;
    }

    public function wastage_details()
    {
        return $this->hasMany(WastageDetail::class, 'wastage_id', 'id');
    }

    public function auditor()
    {
        return $this->belongsTo(Employee::class, 'auditor_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'auditor_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function approved_admin()
    {
        return $this->belongsTo(OrganizationUser::class, 'approved_by', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(OrganizationUser::class, 'created_by', 'id');
    }
}
