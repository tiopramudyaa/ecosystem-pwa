@props(['status' => null])
@php
    $map = [
        'open' => 'bg-blue-50 text-blue-700 border-blue-200',
        'inprocess' => 'bg-amber-50 text-amber-700 border-amber-200',
        'waiting_on_customer' => 'bg-purple-50 text-purple-700 border-purple-200',
        'waiting_on_3rd_party' => 'bg-purple-50 text-purple-700 border-purple-200',
        'waiting_to_confirmation' => 'bg-purple-50 text-purple-700 border-purple-200',
        'hold' => 'bg-gray-100 text-gray-600 border-gray-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
        'closed' => 'bg-green-50 text-green-700 border-green-200',
    ];
    $key = strtolower((string) ($status ?? ''));
    $classes = $map[$key] ?? 'bg-gray-100 text-gray-600 border-gray-200';
@endphp
<span {{ $attributes->merge(['class' => "inline-block px-2.5 py-0.5 rounded-full text-xs font-medium border $classes"]) }}>
    {{ $status ? ucwords(str_replace('_', ' ', $status)) : '-' }}
</span>
