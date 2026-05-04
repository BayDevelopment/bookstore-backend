<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    // =====================
    // REGISTER
    // =====================
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'nim' => 'required|digits_between:5,20|unique:users,nim',

            // 🔥 VALIDASI WA
            'phone' => [
                'required',
                'regex:/^(?:\+62|62|0)?8[1-9][0-9]{7,11}$/'
            ],
        ], [
            'phone.regex' => 'Format nomor WhatsApp tidak valid',
        ]);

        // 🔥 NORMALISASI NOMOR WA
        $phone = $request->phone;

        // hapus semua selain angka
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // ubah ke format 62
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) != '62') {
            $phone = '62' . $phone;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'nim' => $request->nim,
            'phone' => $phone,
            'fakultas' => $request->fakultas,
            'prodi' => $request->prodi,
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Silakan cek email untuk verifikasi akun'
        ]);
    }

    // =====================
    // LOGIN
    // =====================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = Auth::user();

        // ❗ CEK EMAIL VERIFIED
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email belum diverifikasi'
            ], 403);
        }

        // buat token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }

    // =====================
    // LOGOUT
    // =====================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    // =====================
    // VERIFY EMAIL
    // =====================
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect(env('FRONTEND_URL') . '/login?verified=1');
    }

    // =====================
    // RESEND EMAIL
    // =====================
    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah diverifikasi'
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email verifikasi dikirim ulang'
        ]);
    }
}
