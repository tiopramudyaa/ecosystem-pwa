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

    public function pollMessages(Request $request, int $id)
    {
        $afterId = (int) $request->query('after_id', 0);

        $response = $this->liteApi->get("/tickets/{$id}/messages");

        if ($this->handleUnauthorized($response) || ! $response->successful()) {
            return response()->json(['success' => false, 'messages' => []], 401);
        }

        $currentUserId = session('lite_api_user')['id'] ?? null;
        $currentUserRoleIds = session('lite_api_user')['role_ids'] ?? [];
        $isAdmin = in_array(1, $currentUserRoleIds, false);
        $lastSenderState = new \stdClass();
        $lastSenderState->value = null;
        $initialsOf = function ($name) {
            $name = trim((string) $name);
            if ($name === '') {
                return '?';
            }
            return collect(explode(' ', $name))->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
        };
        $formatMessageTime = function ($value) {
            if (empty($value)) {
                return '-';
            }
            try {
                $date = \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'));
                return $date->isToday() ? $date->format('H:i') : $date->format('d M, H:i');
            } catch (\Exception $e) {
                return $value;
            }
        };

        $newMessages = collect($response->json('data', []))
            ->filter(fn ($message) => (int) ($message['id'] ?? 0) > $afterId)
            ->values();

        $html = $newMessages->map(fn ($message) => view('tickets.partials.message-item', [
            'message' => $message,
            'currentUserId' => $currentUserId,
            'isAdmin' => $isAdmin,
            'formatMessageTime' => $formatMessageTime,
            'initialsOf' => $initialsOf,
            'lastSenderState' => $lastSenderState,
        ])->render())->implode('');

        return response()->json([
            'success' => true,
            'html' => $html,
            'last_id' => $newMessages->isNotEmpty() ? (int) $newMessages->last()['id'] : $afterId,
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
            'reply_to_id' => ['nullable', 'integer'],
            'ticket_status' => ['nullable', 'in:inprocess,waiting_on_customer,waiting_to_confirmation,waiting_on_3rd_party,hold'],
            'to_emails' => ['nullable', 'array'],
            'to_emails.*' => ['string'],
            'cc_emails' => ['nullable', 'array'],
            'cc_emails.*' => ['string'],
            'mentioned_employee_ids' => ['nullable', 'array'],
            'mentioned_employee_ids.*' => ['integer'],
            'mentioned_role_ids' => ['nullable', 'array'],
            'mentioned_role_ids.*' => ['integer'],
        ]);

        $isReply = ($validated['message_type'] ?? 'reply') !== 'internal_note';

        $payload = [
            'message' => $validated['message'],
        ];

        if (! empty($validated['message_type'])) {
            $payload['message_type'] = $validated['message_type'];
        }

        if (! empty($validated['reply_to_id'])) {
            $payload['reply_to_id'] = $validated['reply_to_id'];
        }

        if (! empty($validated['ticket_status'])) {
            $payload['ticket_status'] = $validated['ticket_status'];
        }

        // to_emails/cc_emails are only forwarded when the user explicitly edited
        // them via the recipients editor — an empty array is a valid "clear all
        // recipients" instruction to the Lite API, so we must not send it by
        // default just because the composer field happens to be empty.
        if ($isReply && $request->boolean('to_emails_touched')) {
            $payload['to_emails'] = $validated['to_emails'] ?? [];
        }

        if ($isReply && $request->boolean('cc_emails_touched')) {
            $payload['cc_emails'] = $validated['cc_emails'] ?? [];
        }

        // Mentions are only meaningful for internal notes — the Lite API ignores
        // them for reply, but we still avoid sending customer-facing payloads
        // with employee/role ids attached.
        if (! $isReply) {
            $payload['mentioned_employee_ids'] = $validated['mentioned_employee_ids'] ?? [];
            $payload['mentioned_role_ids'] = $validated['mentioned_role_ids'] ?? [];
        }

        $response = $this->liteApi->post("/tickets/{$id}/messages", $payload);

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

        if ($response->json('email_failed') === true) {
            return back()->with('status_warning', $response->json(
                'email_error',
                'Pesan tersimpan, tetapi gagal dikirim ke email customer.'
            ));
        }

        return back()->with('status', 'Pesan berhasil dikirim.');
    }

    public function mentionable(Request $request)
    {
        $response = $this->liteApi->get('/employees/mentionable', [
            'q' => $request->query('q', ''),
        ]);

        if ($this->handleUnauthorized($response)) {
            return response()->json(['success' => false], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function updateNote(Request $request, int $id, int $messageId)
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $response = $this->liteApi->post("/tickets/{$id}/messages/{$messageId}/internal-note", $validated);

        if ($this->handleUnauthorized($response)) {
            return response()->json(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function destroyNote(Request $request, int $id, int $messageId)
    {
        $response = $this->liteApi->delete("/tickets/{$id}/messages/{$messageId}/internal-note");

        if ($this->handleUnauthorized($response)) {
            return response()->json(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function attachment(Request $request, int $attachmentId)
    {
        $response = $this->liteApi->get("/attachments/{$attachmentId}");

        if ($this->handleUnauthorized($response)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi berakhir, silakan login kembali.',
            ]);
        }

        if (! $response->successful()) {
            abort($response->status(), 'Gagal memuat lampiran.');
        }

        return response($response->body(), 200)
            ->header('Content-Type', $response->header('Content-Type', 'application/octet-stream'))
            ->header('Content-Disposition', $response->header('Content-Disposition', 'inline'))
            ->header('Cache-Control', 'private, max-age=3600');
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
