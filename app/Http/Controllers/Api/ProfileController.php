<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'data' => [
                'user' => [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'nim'               => $user->nim,
                    'phone'             => $user->phone,
                    'role'              => $user->role,
                    'fakultas'          => $user->fakultas,
                    'prodi'             => $user->prodi,
                    'fakultas_id'       => $user->fakultas_id,
                    'prodi_id'          => $user->prodi_id,
                    'email_verified_at' => $user->email_verified_at, // ✅
                ],
                'stats' => [
                    'total_orders' => $user->orders()->count(),
                    'total_cart'   => $user->carts()->count(),
                    'total_done'   => $user->orders()->where('status', 'confirmed')->count(),
                ],
            ]
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'nim'         => 'nullable|string',
            'phone'       => 'nullable|string',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'prodi_id'    => 'nullable|exists:prodi,id',
        ]);

        $request->user()->update(
            $request->only('name', 'nim', 'phone', 'fakultas_id', 'prodi_id')
        );

        $user = $request->user()->fresh();

        return response()->json([
            'data' => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'nim'               => $user->nim,
                'phone'             => $user->phone,
                'role'              => $user->role,
                'fakultas'          => $user->fakultas,
                'prodi'             => $user->prodi,
                'fakultas_id'       => $user->fakultas_id,
                'prodi_id'          => $user->prodi_id,
                'email_verified_at' => $user->email_verified_at, // ✅
            ]
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => [
                'required',
                'min:8',
                'confirmed',
                'different:current_password',
            ],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                'errors' => ['current_password' => ['Password lama salah.']]
            ], 422);
        }

        $request->user()->update(['password' => Hash::make($request->password)]);

        // Logout semua perangkat lain
        $request->user()->tokens()
            ->where('id', '!=', $request->user()->currentAccessToken()->id)
            ->delete();

        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Berhasil logout dari semua perangkat.']);
    }
}
