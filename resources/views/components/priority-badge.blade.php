@props(['priority' => null])
@php
    $map = [
        'very high' => 'bg-red-50 text-red-700 border-red-200',
        'high' => 'bg-orange-50 text-orange-700 border-orange-200',
        'medium' => 'bg-amber-50 text-amber-700 border-amber-200',
        'low' => 'bg-green-50 text-green-700 border-green-200',
    ];
    $key = strtolower((string) ($priority ?? ''));
    $classes = $map[$key] ?? 'bg-gray-100 text-gray-600 border-gray-200';
@endphp
<span {{ $attributes->merge(['class' => "inline-block px-2.5 py-0.5 rounded-full text-xs font-medium border $classes"]) }}>
    {{ $priority ?: '-' }}
</span>
