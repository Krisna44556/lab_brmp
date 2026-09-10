<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestServiceItem extends Model
{
    protected $fillable = [
        'sample_request_id',
        'lab_service_id',
        'quantity',
        'price_at_time',
    ];

    public function labService()
    {
        return $this->belongsTo(LabService::class);
    }
}
