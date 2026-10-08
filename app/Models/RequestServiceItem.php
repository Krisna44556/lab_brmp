<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestServiceItem extends Model
{
    protected $fillable = [
        'sample_id',
        'lab_service_id',
        'quantity',
        'price_at_time',
    ];

    // Relasi balik ke Sampel Fisik (Wadah)
    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }
    
    public function labService()
    {
        return $this->belongsTo(LabService::class);
    }
}
