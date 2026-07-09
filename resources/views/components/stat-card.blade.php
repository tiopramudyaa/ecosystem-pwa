@props(['label' => '', 'value' => '-', 'icon' => 'fa-chart-simple', 'color' => 'red'])
@php
    $palette = [
        'red' => ['card' => 'bg-red-50/60 border-red-100', 'icon' => 'bg-red-500 text-white', 'label' => 'text-red-700', 'bar' => 'bg-red-500'],
        'blue' => ['card' => 'bg-blue-50/60 border-blue-100', 'icon' => 'bg-blue-500 text-white', 'label' => 'text-blue-700', 'bar' => 'bg-blue-500'],
        'amber' => ['card' => 'bg-amber-50/60 border-amber-100', 'icon' => 'bg-amber-500 text-white', 'label' => 'text-amber-700', 'bar' => 'bg-amber-500'],
        'green' => ['card' => 'bg-green-50/60 border-green-100', 'icon' => 'bg-green-500 text-white', 'label' => 'text-green-700', 'bar' => 'bg-green-500'],
        'purple' => ['card' => 'bg-purple-50/60 border-purple-100', 'icon' => 'bg-purple-500 text-white', 'label' => 'text-purple-700', 'bar' => 'bg-purple-500'],
        'gray' => ['card' => 'bg-gray-50 border-gray-200', 'icon' => 'bg-gray-500 text-white', 'label' => 'text-gray-600', 'bar' => 'bg-gray-400'],
    ];
    $c = $palette[$color] ?? $palette['red'];
@endphp
<div class="relative overflow-hidden rounded-xl sm:rounded-2xl border {{ $c['card'] }} px-2 py-1.5 sm:px-5 sm:py-4 flex items-center gap-1.5 sm:gap-4 min-w-0 transition-all duration-200 hover:shadow-md sm:hover:-translate-y-0.5">
    <span class="absolute inset-y-0 left-0 w-1 {{ $c['bar'] }}"></span>
    <span class="hidden sm:flex w-12 h-12 rounded-xl items-center justify-center shrink-0 text-lg shadow-sm {{ $c['icon'] }}">
        <i class="fas {{ $icon }}"></i>
    </span>
    <div class="min-w-0">
        <strong class="block text-base sm:text-3xl font-extrabold text-gray-900 leading-tight">{{ $value }}</strong>
        <span class="text-[10px] sm:text-sm font-medium truncate block sm:mt-0.5 {{ $c['label'] }}">{{ $label }}</span>
    </div>
</div>
