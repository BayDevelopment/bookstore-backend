<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakultasModel extends Model
{
    protected $table = 'fakultas';

    protected $fillable = [
        'kode_fakultas',
        'nama_fakultas',
        'deskripsi',
        'is_active',
    ];

    public function prodi()
    {
        return $this->hasMany(ProdiModel::class, 'fakultas_id');
    }
}
