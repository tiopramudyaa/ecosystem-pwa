<?php

namespace App\Http\Controllers;

use App\Services\LiteApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    public function __construct(protected LiteApiClient $liteApi)
    {
    }

    public function index(Request $request)
    {
        $response = $this->liteApi->get('/profile');

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'profile' => $response->json('message', 'Gagal memuat profil.'),
            ]);
        }

        return view('profile.index', [
            'profile' => $response->json('data'),
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $response = $this->liteApi->patch('/profile/change-password', $validated);

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors(
                $response->json('errors') ?? ['password' => $response->json('message', 'Gagal mengubah password.')]
            );
        }

        return back()->with('status', 'Password berhasil diubah.');
    }

    protected function handleUnauthorized($response): bool
    {
        if ($response->status() === 401) {
            Session::forget(['lite_api_token', 'lite_api_user']);

            return true;
        }

        return false;
    }
}
