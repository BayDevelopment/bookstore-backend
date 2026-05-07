<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FakultasModel;
use App\Models\ProdiModel;
use Illuminate\Http\Request;

class FakultasController extends Controller
{
    public function fakultas()
    {
        $fakultas = FakultasModel::where('is_active', true)
            ->select('id', 'nama_fakultas', 'kode_fakultas')
            ->orderBy('nama_fakultas')
            ->get();

        return response()->json(['data' => $fakultas]);
    }

    public function prodi()
    {
        $prodi = ProdiModel::select('id', 'fakultas_id', 'nama_prodi', 'kode_prodi')
            ->orderBy('nama_prodi')
            ->get();

        return response()->json(['data' => $prodi]);
    }
}
