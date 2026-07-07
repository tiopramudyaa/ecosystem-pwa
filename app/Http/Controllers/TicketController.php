<?php

namespace App\Http\Controllers;

use App\Services\LiteApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TicketController extends Controller
{
    public function __construct(protected LiteApiClient $liteApi)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only(['page', 'per_page', 'status', 'priority', 'search']);
        $scope = $request->query('scope') === 'my' ? 'my' : 'all';

        $endpoint = $scope === 'my' ? '/tickets/my' : '/tickets';

        $response = $this->liteApi->get($endpoint, $filters);

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'tickets' => $response->json('message', 'Gagal memuat daftar tiket.'),
            ]);
        }

        $stats = $this->liteApi->get('/tickets/statistics');

        return view('tickets.index', [
            'tickets' => $response->json('data', []),
            'meta' => $response->json('meta', []),
            'stats' => $stats->successful() ? $stats->json('data', []) : [],
            'filters' => $filters,
            'scope' => $scope,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $response = $this->liteApi->get("/tickets/{$id}");

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if ($response->status() === 404) {
            abort(404, $response->json('message', 'Ticket not found.'));
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'tickets' => $response->json('message', 'Gagal memuat detail tiket.'),
            ]);
        }

        $highlightMessageId = $request->query('highlight_message_id');

        $messages = $this->liteApi->get("/tickets/{$id}/messages", array_filter([
            'highlight_message_id' => $highlightMessageId,
        ]));

        return view('tickets.show', [
            'ticket' => $response->json('data'),
            'messages' => $messages->successful() ? $messages->json('data', []) : [],
            'requestedHighlightMessageId' => $highlightMessageId,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $response = $this->liteApi->patch("/tickets/{$id}/status", $validated);

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'status' => $response->json('message', 'Gagal mengubah status tiket.'),
            ]);
        }

        return back()->with('status', 'Status tiket berhasil diubah.');
    }

    public function storeMessage(Request $request, int $id)
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
            'message_type' => ['nullable', 'in:reply,internal_note'],
        ]);

        $response = $this->liteApi->post("/tickets/{$id}/messages", $validated);

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withErrors([
                'message' => $response->json('message', 'Gagal mengirim pesan.'),
            ]);
        }

        return back()->with('status', 'Pesan berhasil dikirim.');
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
