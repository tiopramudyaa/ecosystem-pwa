@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Welcome back')

@php
    $firstName = trim(explode(' ', $user['name'] ?? '')[0] ?? '') ?: 'there';
    $hour = (int) now()->format('G');
    $greeting = $hour < 11 ? 'Good morning' : ($hour < 15 ? 'Good afternoon' : ($hour < 19 ? 'Good evening' : 'Good night'));

    $kpiIcons = [
        'staging_pending' => ['fa-inbox', 'amber'],
        'unassigned_count' => ['fa-user-slash', 'gray'],
        'very_high_count' => ['fa-triangle-exclamation', 'red'],
        'as_pic_count' => ['fa-user-check', 'blue'],
        'active_count' => ['fa-bolt', 'green'],
        'timesheet_pending' => ['fa-clock', 'purple'],
    ];

    $priorityIcons = [
        'very_high' => ['fa-triangle-exclamation', 'red'],
        'high' => ['fa-arrow-up', 'amber'],
        'medium' => ['fa-minus', 'blue'],
        'low' => ['fa-arrow-down', 'green'],
    ];

    $breakdownIcon = fn (string $key, array $map) => $map[strtolower(str_replace(' ', '_', $key))] ?? ['fa-chart-simple', 'gray'];
@endphp

@section('content')
    <div class="relative overflow-hidden primary-gradient rounded-xl shadow-sm px-5 py-5 sm:px-6 sm:py-6 mb-6 text-white">
        <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white opacity-5"></div>
        <div class="absolute -right-16 bottom-0 w-56 h-56 rounded-full bg-white opacity-5"></div>
        <div class="relative flex flex-wrap items-center gap-3 justify-between">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-bold">{{ $greeting }}, {{ $firstName }}</h2>
                    @if (!empty($user['role']['name']))
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium border border-white/30 bg-white/10">
                            {{ $user['role']['name'] }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-white/70 mt-1">{{ now()->translatedFormat('l, j F Y') }} &mdash; {{ $user['name'] ?? '-' }}</p>
            </div>
        </div>
    </div>

    @if (collect(array_keys($kpiIcons))->some(fn ($key) => isset($data[$key])))
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            @foreach ($kpiIcons as $key => [$icon, $color])
                @isset($data[$key])
                    <x-stat-card :label="ucwords(str_replace('_', ' ', $key))" :value="$data[$key]" :icon="$icon" :color="$color" />
                @endisset
            @endforeach
        </div>
    @endif

    @isset($data['priority_breakdown'])
        @php
            $priorityBarColors = [
                'very_high' => 'bg-red-500',
                'high' => 'bg-amber-500',
                'medium' => 'bg-blue-500',
                'low' => 'bg-green-500',
            ];
            $priorityTextColors = [
                'very_high' => 'text-red-600',
                'high' => 'text-amber-600',
                'medium' => 'text-blue-600',
                'low' => 'text-green-600',
            ];
            $priorityTotal = collect($data['priority_breakdown'])->sum() ?: 1;
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6 transition-shadow hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-semibold text-gray-500 tracking-wide uppercase">Priority Breakdown</h3>
                <span class="text-xs text-gray-400">{{ $priorityTotal }} tickets</span>
            </div>

            <div class="flex w-full h-2.5 rounded-full overflow-hidden bg-gray-100 mb-5">
                @foreach ($data['priority_breakdown'] as $label => $value)
                    @php $key = strtolower(str_replace(' ', '_', $label)); @endphp
                    @if ($value > 0)
                        <div class="{{ $priorityBarColors[$key] ?? 'bg-gray-400' }}" style="width: {{ (($value / $priorityTotal) * 100) }}%"></div>
                    @endif
                @endforeach
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach ($data['priority_breakdown'] as $label => $value)
                    @php
                        $key = strtolower(str_replace(' ', '_', $label));
                        $pct = round(($value / $priorityTotal) * 100);
                    @endphp
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $priorityBarColors[$key] ?? 'bg-gray-400' }}"></span>
                        <div class="min-w-0">
                            <div class="flex items-baseline gap-1.5">
                                <strong class="text-lg font-bold text-gray-900 leading-none">{{ $value }}</strong>
                                <span class="text-[11px] {{ $priorityTextColors[$key] ?? 'text-gray-500' }} font-medium">{{ $pct }}%</span>
                            </div>
                            <span class="text-xs text-gray-500 truncate block mt-0.5">{{ ucwords(str_replace('_', ' ', $label)) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endisset

    @isset($data['sla_summary'])
        <div class="mb-6">
            <x-breakdown-row title="SLA Summary" :items="$data['sla_summary']" />
        </div>
    @endisset

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        @isset($data['recent_tickets'])
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-5 transition-shadow hover:shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-red-50 text-red-700 flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-xs"></i>
                        </span>
                        Recent Tickets
                    </h3>
                    <a href="{{ route('tickets.index') }}" class="text-xs primary-text font-medium hover:underline">View all &rarr;</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($data['recent_tickets'] as $ticket)
                        <a href="{{ route('tickets.show', $ticket['ticket_id'] ?? '') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">#{{ $ticket['ticket_number'] ?? '-' }} <span class="font-normal text-gray-600">- {{ \Illuminate\Support\Str::limit($ticket['description'] ?? '-', 50) }}</span></p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $ticket['customer_name'] ?? '-' }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap shrink-0">
                                <x-priority-badge :priority="$ticket['ticket_priority'] ?? null" />
                                <x-status-badge :status="$ticket['status'] ?? null" />
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No recent tickets.</p>
                    @endforelse
                </div>
            </div>
        @endisset

        <div class="{{ isset($data['recent_tickets']) ? '' : 'lg:col-span-3' }} space-y-4">
            @isset($data['team_load'])
                @php $maxLoad = collect($data['team_load'])->max('open_count') ?: 1; @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 transition-shadow hover:shadow-md">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">Agent Workload</h3>
                    <p class="text-xs text-gray-400 mb-4">Active tickets per agent</p>
                    <div class="space-y-3">
                        @foreach ($data['team_load'] as $member)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-700 font-medium">{{ $member['name'] ?? '-' }}</span>
                                    <span class="text-gray-500">{{ $member['open_count'] ?? 0 }}</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full primary-gradient" style="width: {{ (int) round((($member['open_count'] ?? 0) / $maxLoad) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endisset

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 transition-shadow hover:shadow-md">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Quick Navigation</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('tickets.index') }}" class="rounded-xl bg-red-50 hover:bg-red-100 transition-colors p-4 text-center">
                        <i class="fas fa-ticket-alt primary-text text-lg mb-2"></i>
                        <span class="block text-xs font-medium text-gray-700">Tickets</span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="rounded-xl bg-amber-50 hover:bg-amber-100 transition-colors p-4 text-center">
                        <i class="fas fa-bell text-amber-600 text-lg mb-2"></i>
                        <span class="block text-xs font-medium text-gray-700">Notifications</span>
                    </a>
                    <a href="{{ route('profile.index') }}" class="rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors p-4 text-center">
                        <i class="fas fa-user text-blue-600 text-lg mb-2"></i>
                        <span class="block text-xs font-medium text-gray-700">Profile</span>
                    </a>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); openLogoutModal('logout-form');" class="rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors p-4 text-center">
                        <i class="fas fa-sign-out-alt text-gray-500 text-lg mb-2"></i>
                        <span class="block text-xs font-medium text-gray-700">Logout</span>
                    </a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    @isset($data['urgent_tickets'])
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-red-50 text-red-700 flex items-center justify-center">
                        <i class="fas fa-triangle-exclamation text-xs"></i>
                    </span>
                    Urgent Tickets
                </h3>
                <span class="text-xs text-gray-400">{{ count($data['urgent_tickets']) }} tickets</span>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($data['urgent_tickets'] as $ticket)
                    <a href="{{ route('tickets.show', $ticket['ticket_id'] ?? '') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition-colors border-l-2 border-transparent hover:border-red-300">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900">#{{ $ticket['ticket_number'] ?? '-' }} <span class="font-normal text-gray-600">- {{ \Illuminate\Support\Str::limit($ticket['description'] ?? '-', 50) }}</span></p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $ticket['customer_name'] ?? '-' }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                            <x-priority-badge :priority="$ticket['ticket_priority'] ?? null" />
                            <x-status-badge :status="$ticket['status'] ?? null" />
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">No urgent tickets.</p>
                @endforelse
            </div>
        </div>
    @endisset
@endsection
