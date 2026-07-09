@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    @if (!empty($stats))
        @php
            $statMeta = [
                'total' => ['icon' => 'fa-layer-group', 'color' => 'gray'],
                'open' => ['icon' => 'fa-envelope-open-text', 'color' => 'blue'],
                'inprocess' => ['icon' => 'fa-spinner', 'color' => 'amber'],
                'waiting_on_customer' => ['icon' => 'fa-user-clock', 'color' => 'purple'],
                'waiting_on_3rd_party' => ['icon' => 'fa-people-arrows', 'color' => 'purple'],
                'waiting_to_confirmation' => ['icon' => 'fa-circle-question', 'color' => 'amber'],
                'hold' => ['icon' => 'fa-pause', 'color' => 'gray'],
                'cancelled' => ['icon' => 'fa-ban', 'color' => 'red'],
                'closed' => ['icon' => 'fa-circle-check', 'color' => 'green'],
            ];
        @endphp
        <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-4 gap-1.5 sm:gap-3 mb-3 sm:mb-6">
            @foreach ($stats as $label => $value)
                <x-stat-card :label="ucwords(str_replace('_', ' ', $label))" :value="$value"
                             icon="{{ $statMeta[$label]['icon'] ?? 'fa-ticket-alt' }}"
                             color="{{ $statMeta[$label]['color'] ?? 'blue' }}" />
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-2 sm:inline-grid sm:grid-cols-2 gap-2 mb-4">
        <a href="{{ route('tickets.index', array_merge($filters, ['scope' => 'all'])) }}"
           class="text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ ($scope ?? 'all') === 'all' ? 'primary-gradient text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            Semua / Unassigned
        </a>
        <a href="{{ route('tickets.index', array_merge($filters, ['scope' => 'my'])) }}"
           class="text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ ($scope ?? 'all') === 'my' ? 'primary-gradient text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            Tiket Saya
        </a>
    </div>

    <form method="GET" action="{{ route('tickets.index') }}" class="bg-white border border-gray-200 rounded-xl p-3 mb-4 space-y-2">
        <input type="hidden" name="scope" value="{{ $scope ?? 'all' }}">
        <div class="relative w-full">
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" name="search" placeholder="Cari nomor/deskripsi" value="{{ $filters['search'] ?? '' }}"
                   class="primary-focus bg-white text-sm pl-8 w-full">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <select name="status" class="primary-focus bg-white text-sm w-full">
                <option value="">Semua Status</option>
                @foreach (['open','inprocess','waiting_on_customer','waiting_on_3rd_party','waiting_to_confirmation','hold','cancelled','closed'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <select name="priority" class="primary-focus bg-white text-sm w-full">
                <option value="">Semua Prioritas</option>
                @foreach (['Very High','High','Medium','Low'] as $priority)
                    <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ $priority }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg primary-gradient text-white text-sm font-medium w-full">
            <i class="fas fa-filter mr-1.5 text-xs"></i>Filter
        </button>
    </form>

    {{-- Mobile & tablet: card list --}}
    <div class="lg:hidden space-y-3 mb-4">
        @forelse ($tickets as $ticket)
            <a href="{{ route('tickets.show', $ticket['ticket_id']) }}"
               class="block bg-white rounded-xl shadow-sm border border-gray-200 p-3.5 hover:border-gray-300 transition-colors">
                <div class="flex items-start justify-between gap-2 mb-1.5">
                    <span class="primary-text font-semibold text-sm">{{ $ticket['ticket_number'] ?? '-' }}</span>
                    <x-priority-badge :priority="$ticket['ticket_priority'] ?? null" />
                </div>
                <p class="text-sm text-gray-800 mb-2 line-clamp-2 break-words">{{ $ticket['description'] ?? '-' }}</p>
                <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 mb-2">
                    <span class="inline-flex items-center gap-1 min-w-0">
                        <i class="fas fa-building text-[10px] text-gray-400 shrink-0"></i>
                        <span class="truncate">{{ $ticket['customer']['customer_name'] ?? '-' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1 min-w-0">
                        <i class="fas fa-user text-[10px] text-gray-400 shrink-0"></i>
                        <span class="truncate">{{ $ticket['pic']['employee_name'] ?? '-' }}</span>
                    </span>
                </div>
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100">
                    <x-status-badge :status="$ticket['status'] ?? null" />
                    <span class="text-[11px] text-gray-400 truncate">SLA: {{ $ticket['sla']['resolution_status'] ?? '-' }}</span>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 py-10 text-gray-400 text-center">
                <i class="fas fa-inbox text-2xl mb-2 block"></i>
                Tidak ada tiket.
            </div>
        @endforelse
    </div>

    {{-- Desktop: table --}}
    <div class="hidden lg:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto mb-4">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Number</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Description</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Status</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Priority</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Customer</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">PIC</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">SLA</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <a href="{{ route('tickets.show', $ticket['ticket_id']) }}" class="primary-text font-medium hover:underline">
                                {{ $ticket['ticket_number'] ?? '-' }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-gray-800 max-w-xs truncate">{{ $ticket['description'] ?? '-' }}</td>
                        <td class="px-4 py-2"><x-status-badge :status="$ticket['status'] ?? null" /></td>
                        <td class="px-4 py-2"><x-priority-badge :priority="$ticket['ticket_priority'] ?? null" /></td>
                        <td class="px-4 py-2 text-gray-800">{{ $ticket['customer']['customer_name'] ?? '-' }}</td>
                        <td class="px-4 py-2 text-gray-800">{{ $ticket['pic']['employee_name'] ?? '-' }}</td>
                        <td class="px-4 py-2 text-gray-600 text-xs">{{ $ticket['sla']['resolution_status'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-gray-400 text-center">
                        <i class="fas fa-inbox text-2xl mb-2 block"></i>
                        Tidak ada tiket.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (!empty($meta))
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm text-gray-600">
            <span>Halaman {{ $meta['current_page'] ?? 1 }} dari {{ $meta['last_page'] ?? 1 }} (total {{ $meta['total'] ?? 0 }})</span>
            <div class="flex gap-2">
                @if (($meta['current_page'] ?? 1) > 1)
                    <a href="{{ route('tickets.index', array_merge($filters, ['scope' => $scope ?? 'all', 'page' => ($meta['current_page'] - 1)])) }}"
                       class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">&laquo; Sebelumnya</a>
                @endif
                @if (($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))
                    <a href="{{ route('tickets.index', array_merge($filters, ['scope' => $scope ?? 'all', 'page' => ($meta['current_page'] + 1)])) }}"
                       class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">Selanjutnya &raquo;</a>
                @endif
            </div>
        </div>
    @endif
@endsection
