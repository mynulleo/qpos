<?php

namespace App\Models;

use App\Models\Base\BaseModel;

class WarrantyClaimLog extends BaseModel
{
    protected $guarded = ['id'];

    protected $logName = "WarrantyClaimLog";

    public function warrantyClaim()
    {
        return $this->belongsTo(WarrantyClaim::class, 'warranty_claim_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
