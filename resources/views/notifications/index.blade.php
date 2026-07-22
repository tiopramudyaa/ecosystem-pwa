@extends('layouts.app')

@section('title', 'Notifications')

@php
    $formatNotifTime = function ($value) {
        if (empty($value)) {
            return '-';
        }
        try {
            return \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'))->diffForHumans();
        } catch (\Exception $e) {
            return $value;
        }
    };
@endphp

@section('content')
    @php $unreadCount = collect($notifications)->filter(fn ($n) => !($n['is_read'] ?? false))->count(); @endphp

    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-full primary-gradient text-white flex items-center justify-center shrink-0">
                <i class="fas fa-bell"></i>
            </span>
            <div>
                <p class="text-sm font-semibold text-gray-900">Notifications</p>
                <p class="text-xs mt-0.5">
                    @if ($unreadCount > 0)
                        <span class="inline-flex items-center gap-1.5 text-gray-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            <span class="font-semibold text-gray-900">{{ $unreadCount }}</span> unread
                        </span>
                    @else
                        <span class="text-gray-400">All notifications read</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PUT')
                <button type="submit"
                        class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        @disabled($unreadCount === 0)>
                    <i class="fas fa-check-double text-xs mr-1.5"></i>Mark All Read
                </button>
            </form>
            <form method="POST" action="{{ route('notifications.bulk-delete') }}" onsubmit="return confirm('Delete all read notifications?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-200 text-xs font-medium text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors">
                    <i class="fas fa-trash text-xs mr-1.5"></i>Delete Read
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-2.5">
        @forelse ($notifications as $notification)
            @php
                $isRead = $notification['is_read'] ?? false;
                $ticketUrl = !empty($notification['ticket_id'])
                    ? route('tickets.show', $notification['ticket_id']) . (!empty($notification['message_id']) ? ('?highlight_message_id=' . $notification['message_id']) : '')
                    : null;
            @endphp
            <div class="relative rounded-xl border p-3.5 flex items-start gap-3 transition-colors {{ $isRead ? 'bg-white border-gray-200' : 'bg-red-50/50 border-red-100' }}">
                @unless ($isRead)
                    <span class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl bg-red-500"></span>
                @endunless

                <span class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $isRead ? 'bg-gray-100 text-gray-400' : 'primary-gradient text-white' }}">
                    <i class="fas {{ $isRead ? 'fa-envelope-open' : 'fa-envelope' }} text-sm"></i>
                </span>

                <div
                    class="flex-1 min-w-0 {{ $ticketUrl ? 'cursor-pointer' : '' }}"
                    @if ($ticketUrl)
                        data-ticket-url="{{ $ticketUrl }}"
                        data-read-url="{{ route('notifications.read', $notification['id']) }}"
                        data-is-read="{{ $isRead ? '1' : '0' }}"
                        onclick="openNotification(this)"
                    @endif
                >
                    <div class="flex items-center flex-wrap gap-x-1.5 gap-y-0.5 text-xs text-gray-500 mb-1">
                        <span class="font-medium text-gray-700">{{ $notification['from_name'] ?? '-' }}</span>
                        <span class="text-gray-300">&middot;</span>
                        <span>{{ $formatNotifTime($notification['created_at'] ?? null) }}</span>
                    </div>
                    <div class="text-sm break-words {{ $isRead ? 'text-gray-600' : 'text-gray-900' }}">
                        @if (!empty($notification['ticket_number']))
                            <span class="inline-block px-1.5 py-0.5 rounded-md bg-gray-100 primary-text text-xs font-semibold align-middle mr-1">{{ $notification['ticket_number'] }}</span>
                        @endif
                        {{ $notification['preview'] ?? '' }}
                    </div>
                </div>

                <div class="flex gap-1.5 shrink-0">
                    @if (!$isRead)
                        <form method="POST" action="{{ route('notifications.read', $notification['id']) }}">
                            @csrf
                            @method('PUT')
                            <button type="submit" title="Mark as read"
                                    class="w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 flex items-center justify-center hover:text-green-600 hover:border-green-300 hover:bg-green-50 transition-colors">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notification['id']) }}" onsubmit="return confirm('Delete this notification?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Delete"
                                class="w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 flex items-center justify-center hover:text-red-600 hover:border-red-300 hover:bg-red-50 transition-colors">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 py-14 text-center text-gray-400">
                <i class="fas fa-bell-slash text-3xl mb-3 block"></i>
                <p class="text-sm">No notifications yet.</p>
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
