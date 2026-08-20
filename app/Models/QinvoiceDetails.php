<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base\BaseModel;

class QinvoiceDetails extends BaseModel
{
    use SoftDeletes;

    protected $connection = 'accessdb';
    protected $table = 'invoice_details';
    protected $guarded = ['id'];
    protected $logName = "QinvoiceDetails";

    public function invoice()
    {
        return $this->belongsTo(Qinvoice::class, 'invoice_id', 'id');
    }
}
