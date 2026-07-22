@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6 flex items-center gap-4">
        <span class="w-16 h-16 rounded-full primary-gradient text-white flex items-center justify-center text-2xl font-semibold shrink-0">
            {{ strtoupper(substr($profile['first_name'] ?? '?', 0, 1)) }}
        </span>
        <div class="min-w-0">
            <h2 class="text-lg font-semibold text-gray-900 truncate">
                {{ $profile['title'] ?? '' }} {{ $profile['first_name'] ?? '' }} {{ $profile['last_name'] ?? '' }}
            </h2>
            <p class="text-sm text-gray-500">{{ $profile['position'] ?? '-' }} &middot; {{ $profile['division'] ?? '-' }}</p>
            <div class="flex flex-wrap gap-1.5 mt-2">
                @forelse ($profile['roles'] ?? [] as $role)
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium border bg-red-50 text-red-700 border-red-200">{{ $role['name'] ?? '-' }}</span>
                @empty
                    <span class="text-xs text-gray-400">No role</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <i class="fas fa-id-card primary-text"></i> Employee Data
            </h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-gray-500">ECI</dt><dd class="text-gray-800 text-right">{{ $profile['eci'] ?? '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Nick Name</dt><dd class="text-gray-800 text-right">{{ $profile['nick_name'] ?? '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Position</dt><dd class="text-gray-800 text-right">{{ $profile['position'] ?? '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Division</dt><dd class="text-gray-800 text-right">{{ $profile['division'] ?? '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Department</dt><dd class="text-gray-800 text-right">{{ $profile['department'] ?? '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Employee Type</dt><dd class="text-gray-800 text-right">{{ $profile['employee_type'] ?? '-' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <i class="fas fa-address-book primary-text"></i> Contact
            </h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Email (Work)</dt><dd class="text-gray-800 text-right break-all">{{ $profile['email_work'] ?? '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Email (Personal)</dt><dd class="text-gray-800 text-right break-all">{{ $profile['email_personal'] ?? '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Cell Phone</dt><dd class="text-gray-800 text-right">{{ $profile['cell_phone'] ?? '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">City</dt><dd class="text-gray-800 text-right">{{ $profile['city'] ?? '-' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 max-w-md">
        <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i class="fas fa-lock primary-text"></i> Change Password
        </h3>
        <form method="POST" action="{{ route('profile.change-password') }}" class="space-y-3">
            @csrf
            @method('PATCH')
            <input type="password" name="password" placeholder="New password (min 8 characters)" required class="primary-focus bg-white text-sm w-full">
            <input type="password" name="password_confirmation" placeholder="Confirm new password" required class="primary-focus bg-white text-sm w-full">
            <button type="submit" class="px-4 py-2 rounded-lg primary-gradient text-white text-sm font-medium">Change Password</button>
        </form>
    </div>
@endsection
