<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleRequest extends Model
{
    protected $fillable = [
        'user_id',
        'request_code',
        'sample_type',
        'sample_quantity',
        'village',
        'district',
        'regency',
        'province',
        'testing_purpose',
        'total_price',
        'payment_status',
    ];

    // Relasi ke User (Pemohon)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Item Parameter
    public function items()
    {
        return $this->hasMany(RequestServiceItem::class);
    }

    public function samples()
    {
        return $this->hasMany(Sample::class, 'sample_request_id');
    }

    public function sampleRequest()
    {
        return $this->belongsTo(SampleRequest::class, 'sample_request_id');
    }
}