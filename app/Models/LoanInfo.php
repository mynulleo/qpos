<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Models\LoanInstallment;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanInfo extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $logName = "LoanInfo";

    public static function generateTrnsID()
    {
        $trnsid = 111;
        $loaninfo = LoanInfo::latest('id')->first(['id', 'trnsid']);
        if ($loaninfo && !empty($loaninfo->trnsid)) {
            if (is_numeric($loaninfo->trnsid)) {
                $trnsid = intval($loaninfo->trnsid) + 1;
            } elseif (preg_match('/^(.*?)(\d+)$/', $loaninfo->trnsid, $matches)) {
                $prefix = $matches[1];
                $num = intval($matches[2]) + 1;
                $trnsid = $prefix . str_pad($num, strlen($matches[2]), '0', STR_PAD_LEFT);
            }
        }
        return $trnsid;
    }

    public function getTrnsDateAttribute($value)
    {
        $startDate = null;
        if ($value) {
            $startDate = date('d M, Y', strtotime($value));
        }

        return $startDate;
    }

    public function getReturnDateAttribute($value)
    {
        $startDate = null;
        if ($value) {
            $startDate = date('d M, Y', strtotime($value));
        }

        return $startDate;
    }

    // file image push
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function approved_admin()
    {
        return $this->belongsTo(OrganizationUser::class, 'approved_by', 'id');
    }

    public function loan_installment()
    {
        return $this->hasMany(LoanInstallment::class, 'loan_info_id', 'id')->oldest('id');
    }
    // date format
}
