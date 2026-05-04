<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[\pL\s]+$/u', // hanya huruf & spasi, no angka/simbol
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',       // validasi format + cek DNS domain
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()        // harus ada huruf
                    ->mixedCase()      // harus ada huruf besar & kecil
                    ->numbers()        // harus ada angka
                    ->symbols()        // harus ada simbol (!@#$)
                    ->uncompromised(), // cek apakah password pernah bocor
            ],
            'nim' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^[0-9]+$/',    // hanya angka
                'unique:users,nim',
            ],
            'fakultas' => [
                'required',
                'string',
                'max:100',
                'in:Teknik,Hukum,Ekonomi,FKIP,Kesehatan,Pertanian', // sesuaikan fakultas
            ],
            'prodi' => [
                'required',
                'string',
                'max:100',
            ],
        ], [
            // Pesan error custom bahasa Indonesia
            'name.required'     => 'Nama wajib diisi',
            'name.min'          => 'Nama minimal 3 karakter',
            'name.max'          => 'Nama maksimal 100 karakter',
            'name.regex'        => 'Nama hanya boleh berisi huruf dan spasi',
            'email.required'    => 'Email wajib diisi',
            'email.email'       => 'Format email tidak valid',
            'email.unique'      => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'nim.required'      => 'NIM wajib diisi',
            'nim.min'           => 'NIM minimal 8 digit',
            'nim.max'           => 'NIM maksimal 20 digit',
            'nim.regex'         => 'NIM hanya boleh berisi angka',
            'nim.unique'        => 'NIM sudah terdaftar',
            'fakultas.required' => 'Fakultas wajib dipilih',
            'fakultas.in'       => 'Fakultas tidak valid',
            'prodi.required'    => 'Program studi wajib dipilih',
        ]);

        $user = User::create([
            'name'     => trim($request->name),
            'email'    => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'nim'      => trim($request->nim),
            'fakultas' => $request->fakultas,
            'prodi'    => $request->prodi,
            'role'     => 'customer',
        ]);

        // Kirim email verifikasi
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Registrasi berhasil! Silakan cek email untuk verifikasi.',
            'user'    => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
        ], [
            'email.required'    => 'Email wajib diisi',
            'email.email'       => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
            'password.min'      => 'Password minimal 8 karakter',
        ]);

        // Cek rate limiting (cegah brute force)
        $key = 'login_attempts_' . $request->ip();
        $attempts = cache()->get($key, 0);

        if ($attempts >= 5) {
            return response()->json([
                'message' => 'Terlalu banyak percobaan login. Coba lagi dalam 5 menit.'
            ], 429);
        }

        $user = User::where('email', mb_strtolower(trim($request->email)))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            cache()->put($key, $attempts + 1, now()->addMinutes(5));
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        // Cek verifikasi email
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email belum diverifikasi. Silakan cek inbox email kamu.',
                'email_verified' => false,
            ], 403);
        }

        cache()->forget($key);
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'        => 'Login berhasil',
            'user'           => $user,
            'token'          => $token,
            'email_verified' => true,
        ]);
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email'    => 'Format email tidak valid',
            'email.exists'   => 'Email tidak ditemukan',
        ]);

        $user = User::where('email', mb_strtolower(trim($request->email)))->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah diverifikasi, silakan login.'
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email verifikasi dikirim ulang, silakan cek inbox.'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }
}
