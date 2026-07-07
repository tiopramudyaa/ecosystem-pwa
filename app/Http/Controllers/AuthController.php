<?php

namespace App\Http\Controllers;

use App\Services\LiteApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function __construct(protected LiteApiClient $liteApi)
    {
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $response = $this->liteApi->post('/auth/login', $credentials);
        $body = $response->json();

        if (! $response->successful() || ! ($body['success'] ?? false)) {
            return back()
                ->withErrors(['email' => $body['message'] ?? 'Login gagal.'])
                ->onlyInput('email');
        }

        if ($body['require_password_change'] ?? false) {
            return back()->with('status', $body['message'] ?? 'Silakan cek email untuk setup password.');
        }

        Session::put('lite_api_token', $body['data']['token']);
        Session::put('lite_api_user', $body['data']['user']);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $this->liteApi->post('/auth/logout');

        Session::forget(['lite_api_token', 'lite_api_user']);

        return redirect()->route('login');
    }

    public function me(Request $request)
    {
        $response = $this->liteApi->get('/auth/me');

        if (! $response->successful()) {
            Session::forget(['lite_api_token', 'lite_api_user']);

            return redirect()->route('login')->withErrors([
                'email' => $response->json('message', 'Sesi berakhir, silakan login kembali.'),
            ]);
        }

        return view('auth.me', ['user' => $response->json('data')]);
    }
}
