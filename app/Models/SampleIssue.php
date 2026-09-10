<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_id',
        'distributor_id',
        'issue_type',
        'description',
    ];

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }
}