@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    @php $unreadCount = collect($notifications)->filter(fn ($n) => !($n['is_read'] ?? false))->count(); @endphp
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-600">
            @if ($unreadCount > 0)
                <span class="font-semibold text-gray-900">{{ $unreadCount }}</span> belum dibaca
            @else
                Semua notifikasi sudah dibaca
            @endif
        </p>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PUT')
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-check-double text-xs mr-1"></i> Tandai Semua Dibaca
                </button>
            </form>
            <form method="POST" action="{{ route('notifications.bulk-delete') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-trash text-xs mr-1"></i> Hapus yang Sudah Dibaca
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-2">
        @forelse ($notifications as $notification)
            @php
                $ticketUrl = !empty($notification['ticket_id'])
                    ? route('tickets.show', $notification['ticket_id']) . (!empty($notification['message_id']) ? ('?highlight_message_id=' . $notification['message_id']) : '')
                    : null;
            @endphp
            <div class="relative rounded-xl border p-4 pl-5 flex items-start justify-between gap-3 {{ ($notification['is_read'] ?? false) ? 'bg-white border-gray-200' : 'bg-blue-50 border-blue-200' }}">
                @unless ($notification['is_read'] ?? false)
                    <span class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl bg-blue-500"></span>
                @endunless
                <div
                    class="flex-1 min-w-0 {{ $ticketUrl ? 'cursor-pointer' : '' }}"
                    @if ($ticketUrl)
                        data-ticket-url="{{ $ticketUrl }}"
                        data-read-url="{{ route('notifications.read', $notification['id']) }}"
                        data-is-read="{{ ($notification['is_read'] ?? false) ? '1' : '0' }}"
                        onclick="openNotification(this)"
                    @endif
                >
                    <div class="flex items-center gap-1.5 text-xs text-gray-500 mb-1">
                        <i class="fas fa-circle-user text-gray-400"></i>
                        <span>{{ $notification['from_name'] ?? '-' }}</span>
                        <span>&middot;</span>
                        <span>{{ $notification['created_at'] ?? '-' }}</span>
                    </div>
                    <div class="text-sm text-gray-800 break-words">
                        @if (!empty($notification['ticket_number']))
                            <strong class="primary-text">{{ $notification['ticket_number'] }}</strong> &mdash;
                        @endif
                        {{ $notification['preview'] ?? '' }}
                    </div>
                </div>
                <div class="flex gap-2 whitespace-nowrap">
                    @if (!($notification['is_read'] ?? false))
                        <form method="POST" action="{{ route('notifications.read', $notification['id']) }}">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="text-xs px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">Tandai Dibaca</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notification['id']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs px-2 py-1 rounded-md bg-white border border-gray-200 text-red-600 hover:bg-red-50">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">
                <i class="fas fa-bell-slash text-2xl mb-2"></i>
                <p class="text-sm">Belum ada notifikasi.</p>
            </div>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script>
        function openNotification(el) {
            var url = el.getAttribute('data-ticket-url');
            var isRead = el.getAttribute('data-is-read') === '1';

            if (isRead) {
                window.location.href = url;
                return;
            }

            var readUrl = el.getAttribute('data-read-url');
            var token = document.querySelector('meta[name="csrf-token"]').content;

            fetch(readUrl, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
            }).catch(function () {}).finally(function () {
                window.location.href = url;
            });
        }
    </script>
@endpush
