<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FakultasModel;
use App\Models\ProdiModel;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nim' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^[0-9]+$/',
                'unique:users,nim',
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[\pL\s]+$/u',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
            ],
            'fakultas_id' => [
                'required',
                'exists:fakultas,id',
            ],
            'prodi_id' => [
                'required',
                'exists:prodi,id',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            'nim.required'         => 'NIM wajib diisi',
            'nim.min'              => 'NIM minimal 8 digit',
            'nim.max'              => 'NIM maksimal 20 digit',
            'nim.regex'            => 'NIM hanya boleh berisi angka',
            'nim.unique'           => 'NIM sudah terdaftar',
            'name.required'        => 'Nama wajib diisi',
            'name.min'             => 'Nama minimal 3 karakter',
            'name.max'             => 'Nama maksimal 100 karakter',
            'name.regex'           => 'Nama hanya boleh berisi huruf dan spasi',
            'email.required'       => 'Email wajib diisi',
            'email.email'          => 'Format email tidak valid',
            'email.unique'         => 'Email sudah terdaftar',
            'fakultas_id.required' => 'Fakultas wajib dipilih',
            'fakultas_id.exists'   => 'Fakultas tidak ditemukan',
            'prodi_id.required'    => 'Program studi wajib dipilih',
            'prodi_id.exists'      => 'Program studi tidak ditemukan',
            'password.required'    => 'Password wajib diisi',
            'password.confirmed'   => 'Konfirmasi password tidak cocok',
        ]);

        $fakultas = FakultasModel::findOrFail($request->fakultas_id);
        $prodi    = ProdiModel::findOrFail($request->prodi_id);

        $user = User::create([
            'nim'         => trim($request->nim),
            'name'        => trim($request->name),
            'email'       => strtolower(trim($request->email)),
            'password'    => Hash::make($request->password),
            'fakultas'    => $fakultas->nama_fakultas,
            'prodi'       => $prodi->nama_prodi,
            'fakultas_id' => $request->fakultas_id,
            'prodi_id'    => $request->prodi_id,
            'role'        => 'customer',
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Registrasi berhasil! Silakan cek email untuk verifikasi.',
            'user'    => [
                'name'  => $user->name,
                'email' => $user->email,
                'nim'   => $user->nim,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ], [
            'email.required'    => 'Email wajib diisi',
            'email.email'       => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
            'password.min'      => 'Password minimal 8 karakter',
        ]);

        $key      = 'login_attempts_' . $request->ip() . '_' . md5($request->email);
        $attempts = cache()->get($key, 0);

        if ($attempts >= 5) {
            return response()->json([
                'message' => 'Terlalu banyak percobaan login. Coba lagi dalam 5 menit.',
            ], 429);
        }

        $user = User::where('email', mb_strtolower(trim($request->email)))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            cache()->put($key, $attempts + 1, now()->addMinutes(5));
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message'        => 'Email belum diverifikasi. Silakan cek inbox email kamu.',
                'email_verified' => false,
            ], 403);
        }

        cache()->forget($key);
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'        => 'Login berhasil',
            'token'          => $token,
            'email_verified' => true,
            'user'           => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'nim'               => $user->nim,
                'role'              => $user->role,
                'fakultas'          => $user->fakultas,
                'prodi'             => $user->prodi,
                'fakultas_id'       => $user->fakultas_id,
                'prodi_id'          => $user->prodi_id,
                'email_verified_at' => $user->email_verified_at, // ✅
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'nim'               => $user->nim,
                'role'              => $user->role,
                'fakultas'          => $user->fakultas,
                'prodi'             => $user->prodi,
                'fakultas_id'       => $user->fakultas_id,
                'prodi_id'          => $user->prodi_id,
                'email_verified_at' => $user->email_verified_at, // ✅
            ],
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
                'message' => 'Email sudah diverifikasi, silakan login.',
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email verifikasi dikirim ulang, silakan cek inbox.',
        ]);
    }

    // ── FORGOT PASSWORD ──────────────────────────────────────────────
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email'    => 'Format email tidak valid',
            'email.exists'   => 'Email tidak ditemukan',
        ]);

        $status = Password::sendResetLink(['email' => mb_strtolower(trim($request->email))]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Link reset password telah dikirim ke email kamu.']);
        }

        return response()->json(['message' => 'Gagal mengirim link reset password.'], 422);
    }

    // ── RESET PASSWORD ───────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email', 'exists:users,email'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'max:255',
                \Illuminate\Validation\Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'token.required'     => 'Token tidak valid.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.exists'       => 'Email tidak ditemukan.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.letters'   => 'Password harus mengandung huruf.',
            'password.mixed'     => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers'   => 'Password harus mengandung angka.',
            'password.symbols'   => 'Password harus mengandung simbol.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'          => Hash::make($password),
                    'remember_token'    => Str::random(60),
                    'email_verified_at' => now(), // ✅ pastikan ini ada
                ])->save();

                $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password berhasil direset. Silakan login.']);
        }

        return response()->json([
            'errors' => ['token' => ['Token tidak valid atau sudah expired.']]
        ], 422);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout',
        ]);
    }
}
