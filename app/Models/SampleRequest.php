<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SampleRequest extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi secara massal kecuali 'id'
    protected $guarded = ['id'];

    // Mengonversi tipe data secara otomatis
    protected $casts = [
        'total_price' => 'decimal:2',
        'status' => 'integer',
        'sample_quantity' => 'integer',
    ];

    // Relasi ke Pemohon (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Daftar Item Layanan/Parameter
    public function items()
    {
        return $this->hasMany(RequestServiceItem::class, 'sample_request_id');
    }

    // Relasi ke Sampel Fisik
    public function samples()
    {
        return $this->hasMany(Sample::class, 'sample_request_id');
    }
}