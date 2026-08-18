<?php

/**
 * @Quill Information Technology
 */

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Admin;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPointTransaction extends BaseModel
{
    use SoftDeletes;

    protected $table = 'client_point_transactions';
    protected $guarded = ['id'];
    protected $logName = 'Client Point Transaction';

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'id');
    }
}
