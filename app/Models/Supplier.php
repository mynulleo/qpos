<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "Supplier";

    public static function generateSupID()
    {
        $supid = 111;
        $lastsupplier = Supplier::latest('id')->first(['id', 'supid']);
        if ($lastsupplier && !empty($lastsupplier->supid)) {
            if (is_numeric($lastsupplier->supid)) {
                $supid = intval($lastsupplier->supid) + 1;
            } elseif (preg_match('/^(.*?)(\d+)$/', $lastsupplier->supid, $matches)) {
                $prefix = $matches[1];
                $num = intval($matches[2]) + 1;
                $supid = $prefix . str_pad($num, strlen($matches[2]), '0', STR_PAD_LEFT);
            }
        }
        return $supid;
    }

    // file image push
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
    // date format
}
