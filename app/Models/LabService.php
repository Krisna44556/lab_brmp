<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabService extends Model
{
    protected $fillable = [
        'lab_category',
        'sub_category',
        'accreditation',
        'service_name',
        'method',
        'equipment',
        'unit',
        'price',
    ];
}
