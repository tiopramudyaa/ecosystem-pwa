@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    <div class="grid grid-cols-2 sm:inline-grid sm:grid-cols-2 gap-2 mb-4">
        <a href="{{ route('tickets.index', array_merge($filters, ['scope' => 'all'])) }}"
           class="text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ ($scope ?? 'all') === 'all' ? 'primary-gradient text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            All / Unassigned
        </a>
        <a href="{{ route('tickets.index', array_merge($filters, ['scope' => 'my'])) }}"
           class="text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ ($scope ?? 'all') === 'my' ? 'primary-gradient text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            My Tickets
        </a>
    </div>

    <form id="ticket-filter-form" method="GET" action="{{ route('tickets.index') }}" class="bg-white border border-gray-200 rounded-xl p-3 mb-4 space-y-2">
        <input type="hidden" name="scope" value="{{ $scope ?? 'all' }}">
        <div class="relative w-full">
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" name="search" id="filter-search" placeholder="Search number/description" value="{{ $filters['search'] ?? '' }}"
                   autocomplete="off"
                   class="primary-focus bg-white text-sm pl-8 pr-3 py-2 w-full border border-gray-200 rounded-lg">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div class="relative">
                <select name="status" class="primary-focus bg-white text-sm w-full border border-gray-200 rounded-lg pl-3 pr-8 py-2 appearance-none">
                    <option value="">All Statuses</option>
                    @foreach (['open','inprocess','waiting_on_customer','waiting_on_3rd_party','waiting_to_confirmation','hold','cancelled','closed'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
            </div>
            <div class="relative">
                <select name="priority" class="primary-focus bg-white text-sm w-full border border-gray-200 rounded-lg pl-3 pr-8 py-2 appearance-none">
                    <option value="">All Priorities</option>
                    @foreach (['Very High','High','Medium','Low'] as $priority)
                        <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ $priority }}</option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
            </div>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg primary-gradient text-white text-sm font-medium w-full">
            <i class="fas fa-filter mr-1.5 text-xs"></i>Filter
        </button>
    </form>

    <div id="tickets-results">
        @include('tickets.partials.results')
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var form = document.getElementById('ticket-filter-form');
        var searchInput = document.getElementById('filter-search');
        var results = document.getElementById('tickets-results');

        if (!form || !results) return;

        var debounceTimer = null;

        function fetchResults(url, pushState) {
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    results.innerHTML = html;
                    if (pushState) window.history.pushState({ ticketsUrl: url }, '', url);
                })
                .catch(function () {
                    window.location.href = url;
                });
        }

        function buildUrl(extraParams) {
            var params = new URLSearchParams(new FormData(form));
            if (extraParams) {
                Object.keys(extraParams).forEach(function (key) {
                    params.set(key, extraParams[key]);
                });
            }
            var query = params.toString();
            return form.action + (query ? '?' + query : '');
        }

        function submitFiltered() {
            fetchResults(buildUrl({ page: 1 }), true);
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submitFiltered();
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(submitFiltered, 400);
            });
        }

        Array.prototype.forEach.call(form.querySelectorAll('select'), function (select) {
            select.addEventListener('change', submitFiltered);
        });

        results.addEventListener('click', function (event) {
            var link = event.target.closest('.pagination-link');
            if (!link) return;
            event.preventDefault();
            fetchResults(link.href, true);
        });

        window.addEventListener('popstate', function () {
            fetchResults(window.location.href, false);
        });
    })();
</script>
@endpush
