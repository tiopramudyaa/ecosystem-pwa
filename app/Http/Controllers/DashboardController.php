<?php

namespace App\Http\Controllers;

use App\Services\LiteApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function __construct(protected LiteApiClient $liteApi)
    {
    }

    public function index(Request $request)
    {
        $response = $this->liteApi->get('/dashboard');

        if (! $response->successful()) {
            if ($response->status() === 401) {
                Session::forget(['lite_api_token', 'lite_api_user']);

                return redirect()->route('login')->withErrors([
                    'email' => 'Your session has expired, please log in again.',
                ]);
            }

            return back()->withErrors([
                'dashboard' => $response->json('message', 'Failed to load dashboard.'),
            ]);
        }

        return view('dashboard', [
            'user' => Session::get('lite_api_user'),
            'data' => $response->json('data'),
        ]);
    }
}
