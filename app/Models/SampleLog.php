<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_id',
        'distributor_id',
        'action',
        'notes',
    ];

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }
}