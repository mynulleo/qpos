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
        $lastsupplier = Supplier::latest()->first(['id', 'supid']);
        if ($lastsupplier) {
            $supid = $lastsupplier->supid + 1;
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
