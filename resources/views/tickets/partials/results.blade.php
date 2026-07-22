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
            No tickets.
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
                    No tickets.
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if (!empty($meta))
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm text-gray-600">
        <span>Page {{ $meta['current_page'] ?? 1 }} of {{ $meta['last_page'] ?? 1 }} (total {{ $meta['total'] ?? 0 }})</span>
        <div class="flex gap-2">
            @if (($meta['current_page'] ?? 1) > 1)
                <a href="{{ route('tickets.index', array_merge($filters, ['scope' => $scope ?? 'all', 'page' => ($meta['current_page'] - 1)])) }}"
                   class="pagination-link px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">&laquo; Previous</a>
            @endif
            @if (($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))
                <a href="{{ route('tickets.index', array_merge($filters, ['scope' => $scope ?? 'all', 'page' => ($meta['current_page'] + 1)])) }}"
                   class="pagination-link px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">Next &raquo;</a>
            @endif
        </div>
    </div>
@endif
