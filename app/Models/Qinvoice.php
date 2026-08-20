<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base\BaseModel;

class Qinvoice extends BaseModel
{
    use SoftDeletes;

    protected $connection = 'accessdb';
    protected $table = 'invoices';
    protected $guarded = ['id'];
    protected $logName = "Qinvoice";

    public static function generateInvoiceNumber()
    {
        $invoiceno = 10001;
        $lastInvoice = self::orderBy('id', 'desc')->first();
        if ($lastInvoice && !empty($lastInvoice->invoice_no)) {
            $lastNumber = intval($lastInvoice->invoice_no);
            if ($lastNumber > 0) {
                $invoiceno = $lastNumber + 1;
            } else {
                $invoiceno = time();
            }
        }
        return (string)$invoiceno;
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function invoice_details()
    {
        return $this->hasMany(QinvoiceDetails::class, 'invoice_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(QinvoiceDetails::class, 'invoice_id', 'id');
    }
}
