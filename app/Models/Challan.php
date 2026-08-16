<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;

class Challan extends BaseModel
{
    protected $guarded = ['id'];

    protected $logName = "Challan";

    public static function generateChallanNumber()
    {
        $challanno = 111;
        // invoices টেবিল থেকে সর্বশেষ ইনভয়েস নাম্বার বের করা
        $lastChallan = self::orderBy('id', 'desc')->first();
        if ($lastChallan) {
            // last invoice number integer এ কনভার্ট
            $lastNumber = intval($lastChallan->challan_no);
            // ১ যোগ করে নতুন নাম্বার রিটার্ন করা
            $challanno =  $lastNumber + 1;
        }
        return $challanno;
    }

    public function getChallanDateAttribute($value)
    {
        $startDate = null;
        if ($value) {
            $startDate = date('d M, Y', strtotime($value));
        }

        return $startDate;
    }

    // relation functions
    public function workorder()
    {
        return $this->belongsTo(Workorder::class, 'workorder_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function challan_details()
    {
        return $this->hasMany(ChallanDetail::class, 'challan_id', 'id')->oldest('id');
    }

    // date format
}
