@php
    $formatDate = function ($value, $withTime = true) {
        if (empty($value) || $value === '-') {
            return '-';
        }
        try {
            $date = \Carbon\Carbon::parse($value)->setTimezone(config('app.timezone'));
            return $withTime ? $date->format('d M Y, H:i') : $date->format('d M Y');
        } catch (\Exception $e) {
            return $value;
        }
    };
@endphp

<div class="flex flex-wrap items-start justify-between gap-3 mb-3">
    <div class="min-w-0">
        <div class="flex items-center flex-wrap gap-2 mb-1">
            <span class="text-sm font-semibold text-gray-500">{{ $ticket['ticket_number'] ?? '-' }}</span>
            <x-status-badge :status="$ticket['status'] ?? null" />
            <x-priority-badge :priority="$ticket['ticket_priority'] ?? null" />
        </div>
        <p class="text-sm text-gray-800 break-words">{{ $ticket['description'] ?? '-' }}</p>
    </div>
    @if (isset($ticket['progress_percentage']))
        <div class="w-full sm:w-40 shrink-0">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Progress</span>
                <span>{{ $ticket['progress_percentage'] }}%</span>
            </div>
            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full primary-gradient" style="width: {{ min(100, max(0, (int) $ticket['progress_percentage'])) }}%"></div>
            </div>
        </div>
    @endif
</div>

<dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-3 text-sm border-t border-gray-100 pt-4">
    <div><dt class="text-xs text-gray-500">Type</dt><dd class="text-gray-800">{{ $ticket['ticket_type'] ?? '-' }}</dd></div>
    <div><dt class="text-xs text-gray-500">Scale</dt><dd class="text-gray-800">{{ $ticket['scale'] ?? '-' }}</dd></div>
    <div><dt class="text-xs text-gray-500">Channel</dt><dd class="text-gray-800">{{ $ticket['channel'] ?? '-' }}</dd></div>
    <div><dt class="text-xs text-gray-500">Start Date</dt><dd class="text-gray-800">{{ $formatDate($ticket['start_date'] ?? null) }}</dd></div>
    <div><dt class="text-xs text-gray-500">End Date</dt><dd class="text-gray-800">{{ $formatDate($ticket['end_date'] ?? null) }}</dd></div>
    <div><dt class="text-xs text-gray-500">Customer</dt><dd class="text-gray-800">{{ $ticket['customer']['customer_name'] ?? '-' }}</dd></div>
    <div><dt class="text-xs text-gray-500">PIC</dt><dd class="text-gray-800">{{ $ticket['pic']['employee_name'] ?? '-' }}</dd></div>
    <div>
        <dt class="text-xs text-gray-500">Members</dt>
        <dd class="text-gray-800">
            @forelse ($ticket['members'] ?? [] as $member)
                {{ $member['employee_name'] ?? '-' }}@if (!$loop->last), @endif
            @empty
                -
            @endforelse
        </dd>
    </div>
    <div><dt class="text-xs text-gray-500">SLA Resolution</dt><dd class="text-gray-800">{{ ucwords(str_replace('_', ' ', $ticket['sla']['resolution_status'] ?? '-')) }} <span class="text-gray-400">(due: {{ $formatDate($ticket['sla']['resolution_due_at'] ?? null) }})</span></dd></div>
    <div><dt class="text-xs text-gray-500">SLA Response</dt><dd class="text-gray-800">{{ ucwords(str_replace('_', ' ', $ticket['sla']['response_status'] ?? '-')) }}</dd></div>
    <div><dt class="text-xs text-gray-500">Created At</dt><dd class="text-gray-800">{{ $formatDate($ticket['created_at'] ?? null) }}</dd></div>
    <div><dt class="text-xs text-gray-500">Updated At</dt><dd class="text-gray-800">{{ $formatDate($ticket['updated_at'] ?? null) }}</dd></div>
</dl>
