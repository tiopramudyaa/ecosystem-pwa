<?php

namespace App\Http\Controllers;

use App\Services\LiteApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    public function __construct(protected LiteApiClient $liteApi)
    {
    }

    public function index(Request $request)
    {
        $response = $this->liteApi->get('/notifications');

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'notifications' => $response->json('message', 'Gagal memuat notifikasi.'),
            ]);
        }

        return view('notifications.index', [
            'notifications' => $response->json('data', []),
            'unreadCount' => $response->json('unread_count', 0),
        ]);
    }

    public function unreadCount(Request $request)
    {
        $response = $this->liteApi->get('/notifications/unread-count');

        if ($response->status() === 401) {
            Session::forget(['lite_api_token', 'lite_api_user']);

            return response()->json(['success' => false], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function markRead(Request $request, int $id)
    {
        $response = $this->liteApi->put("/notifications/{$id}/read");

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'notifications' => $response->json('message', 'Gagal menandai notifikasi.'),
            ]);
        }

        return back();
    }

    public function markAllRead(Request $request)
    {
        $response = $this->liteApi->patch('/notifications/read-all');

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'notifications' => $response->json('message', 'Gagal menandai semua notifikasi.'),
            ]);
        }

        return back()->with('status', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function destroy(Request $request, int $id)
    {
        $response = $this->liteApi->delete("/notifications/{$id}");

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'notifications' => $response->json('message', 'Gagal menghapus notifikasi.'),
            ]);
        }

        return back()->with('status', 'Notifikasi dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $response = $this->liteApi->delete('/notifications/bulk-delete');

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'notifications' => $response->json('message', 'Gagal menghapus notifikasi yang sudah dibaca.'),
            ]);
        }

        return back()->with('status', 'Notifikasi yang sudah dibaca berhasil dihapus.');
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
