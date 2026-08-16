<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = "Client";

    // file image push
    public static function generateClientID()
    {
        $clientid = 111;
        $client = Client::latest()->first(['id', 'clientid']);
        if ($client) {
            $clientid = $client->clientid + 1;
        }
        return $clientid;
    }

    public function getRegDateAttribute($value)
    {
        $startDate = null;
        if ($value) {
            $startDate = date('d M, Y', strtotime($value));
        }

        return $startDate;
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    // date format
}
