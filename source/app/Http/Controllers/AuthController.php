<?php

namespace App\Http\Controllers;

use App\Facades\Auth;
use App\Models\Domain;
use App\Services\VueAppService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $shares = [
          'domains' => Domain::query()->enabled()->pluck('name', 'id'),
        ];

        return VueAppService::render('auth/login', [], $shares);
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Неверный логин или пароль',
                ]);
            }

            throw ValidationException::withMessages([
                'username' => 'Неверный логин или пароль',
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'redirect' => route('dashboard'),
                // here we can send welcome message to username by fullname
            ]);
        }

        return redirect()->intended();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
