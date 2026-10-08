<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    protected $guarded = ['id'];

    public function sampleRequest()
    {
        return $this->belongsTo(SampleRequest::class, 'sample_request_id');
    }

    // RELASI BARU: 1 Sampel punya banyak item parameter uji
    public function serviceItems()
    {
        return $this->hasMany(RequestServiceItem::class, 'sample_id');
    }

    public function logs()
    {
        return $this->hasMany(SampleLog::class);
    }
}