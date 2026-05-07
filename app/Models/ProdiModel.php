<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdiModel extends Model
{
    protected $table = 'prodi';
    protected $fillable = [
        'fakultas_id',
        'kode_prodi',
        'nama_prodi',
    ];

    public function fakultas()
    {
        return $this->belongsTo(FakultasModel::class, 'fakultas_id');
    }
}
