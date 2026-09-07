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
        $client = Client::latest('id')->first(['id', 'clientid']);
        if ($client && !empty($client->clientid)) {
            if (is_numeric($client->clientid)) {
                $clientid = intval($client->clientid) + 1;
            } elseif (preg_match('/^(.*?)(\d+)$/', $client->clientid, $matches)) {
                $prefix = $matches[1];
                $num = intval($matches[2]) + 1;
                $clientid = $prefix . str_pad($num, strlen($matches[2]), '0', STR_PAD_LEFT);
            }
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

    public function pointTransactions()
    {
        return $this->hasMany(ClientPointTransaction::class, 'client_id', 'id')->latest('id');
    }

    // date format
}
