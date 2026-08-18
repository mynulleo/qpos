<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Support\Facades\DB;

class InvoiceDetails extends BaseModel
{
    protected $guarded = ['id'];

    protected $logName = "InvoiceDetails";

    protected static function booted()
    {
        // CREATE
        static::created(function ($model) {
            if (!empty($model->workorder_id)) {
                self::updateWorkorderDue($model->workorder_id);
            }
        });

        // UPDATE
        static::updated(function ($model) {

            $oldId = $model->getOriginal('workorder_id');
            $newId = $model->workorder_id;

            // old workorder (only if exists)
            if (!empty($oldId) && $oldId != $newId) {
                self::updateWorkorderDue($oldId);
            }

            // new workorder (only if exists)
            if (!empty($newId)) {
                self::updateWorkorderDue($newId);
            }
        });

        // DELETE
        static::deleted(function ($model) {
            if (!empty($model->workorder_id)) {
                self::updateWorkorderDue($model->workorder_id);
            }
        });
    }

    // file image push
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function workorder()
    {
        return $this->belongsTo(Workorder::class, 'workorder_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    private static function updateWorkorderDue($workorderId)
    {
        // extra safety
        if (empty($workorderId)) return;

        $totalInvoiced = DB::table('invoice_details')
            ->where('workorder_id', $workorderId)
            ->sum('amount');

        $workorder = DB::table('workorders')
            ->where('id', $workorderId)
            ->first();

        if ($workorder) {

            $due = ($workorder->amount ?? 0) - $totalInvoiced;

            // negative prevent
            $due = max(0, $due);

            DB::table('workorders')
                ->where('id', $workorderId)
                ->update([
                    'due_amount' => $due,
                    'is_closed'  => $due == 0 ? 1 : 0, // ✅ auto close খুলে/বন্ধ হবে
                ]);
        }
    }
}
