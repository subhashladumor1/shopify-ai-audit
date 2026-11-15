@extends('guardian::layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Application Observability & Telemetry</h1>
        <p class="text-sm text-slate-400 mt-1">Live in-flight metrics for HTTP requests, database queries, and external Shopify API calls.</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl">
            <span class="text-xs text-slate-400 uppercase font-semibold">Captured Requests</span>
            <div class="text-2xl font-bold text-white mt-1">{{ count($telemetry['requests']) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl">
            <span class="text-xs text-slate-400 uppercase font-semibold">Executed Queries</span>
            <div class="text-2xl font-bold text-white mt-1">{{ count($telemetry['queries']) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl">
            <span class="text-xs text-slate-400 uppercase font-semibold">Shopify API Calls</span>
            <div class="text-2xl font-bold text-white mt-1">{{ count($telemetry['api_calls']) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl">
            <span class="text-xs text-slate-400 uppercase font-semibold">Exceptions</span>
            <div class="text-2xl font-bold text-red-400 mt-1">{{ count($telemetry['exceptions']) }}</div>
        </div>
    </div>

    <!-- Telemetry Log -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800">
            <h2 class="text-base font-semibold text-white">Recent Query Stream</h2>
        </div>
        @if(empty($telemetry['queries']))
            <div class="p-8 text-center text-slate-500 text-sm">
                No database queries recorded yet. Telemetry will appear as application actions are executed.
            </div>
        @else
            <div class="divide-y divide-slate-800 font-mono text-xs">
                @foreach($telemetry['queries'] as $q)
                    <div class="p-4 hover:bg-slate-800/40 flex justify-between items-center">
                        <span class="text-slate-300 truncate max-w-2xl">{{ $q['sql'] }}</span>
                        <span class="text-slate-500">{{ $q['time_ms'] }} ms</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
