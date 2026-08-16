<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Facades\DB;

class ChallanDetail extends BaseModel
{
    protected $guarded = ['id'];

    protected $logName = "ChallanDetail";

    // relation functions
    public function challan()
    {
        return $this->belongsTo(Challan::class, 'challan_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
    // date format

    // boot function
    protected static function boot()
    {
        parent::boot();

        // CREATE
        static::created(function ($model) {
            self::updateWorkorderDetail($model->workorder_detail_id);
        });

        // UPDATE
        static::updated(function ($model) {
            self::updateWorkorderDetail($model->workorder_detail_id);
        });

        // DELETE
        static::deleted(function ($model) {
            self::updateWorkorderDetail($model->workorder_detail_id);
        });
    }

    private static function updateWorkorderDetail($workorder_detail_id)
    {
        $totalReceive = DB::table('challan_details')
            ->where('workorder_detail_id', $workorder_detail_id)
            ->sum('receive_qty');

        $row = DB::table('workorder_details')
            ->where('id', $workorder_detail_id)
            ->first();

        if ($row) {
            $due = max(0, $row->ordered_qty - $totalReceive);

            DB::table('workorder_details')
                ->where('id', $workorder_detail_id)
                ->update([
                    'receive_qty' => $totalReceive,
                    'due_qty'     => $due,
                ]);
        }
    }
}
