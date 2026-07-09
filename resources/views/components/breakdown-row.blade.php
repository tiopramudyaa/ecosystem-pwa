@props(['title' => '', 'items' => []])
@php
    $dotColors = ['bg-blue-500', 'bg-amber-500', 'bg-purple-500', 'bg-indigo-500', 'bg-emerald-500', 'bg-orange-500', 'bg-gray-400', 'bg-red-500'];
@endphp
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 transition-shadow hover:shadow-md">
    <h3 class="text-xs font-semibold text-gray-500 tracking-wide uppercase mb-4">{{ $title }}</h3>
    <div class="flex flex-wrap gap-x-8 gap-y-4">
        @foreach ($items as $label => $value)
            <div>
                <strong class="block text-2xl font-bold text-gray-900 leading-tight">{{ $value }}</strong>
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 mt-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $dotColors[$loop->index % count($dotColors)] }}"></span>
                    {{ ucwords(str_replace('_', ' ', $label)) }}
                </span>
            </div>
        @endforeach
    </div>
</div>
