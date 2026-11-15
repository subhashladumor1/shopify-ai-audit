@extends('guardian::layouts.app')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-white">Shopify Platform & API Intelligence</h1>
        <p class="text-sm text-slate-400 mt-1">Registry of official Shopify APIs, quarterly version lifecycles, and Partner Governance policies.</p>
    </div>

    <!-- APIs Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800">
            <h2 class="text-base font-semibold text-white">Official Shopify APIs</h2>
        </div>
        <div class="divide-y divide-slate-800">
            @foreach($apis as $api)
                <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-white text-sm">{{ $api['name'] }}</span>
                            <span class="font-mono text-xs px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">{{ $api['latest_stable_version'] }}</span>
                        </div>
                        <p class="text-xs text-slate-400 font-mono">{{ $api['endpoint_pattern'] }}</p>
                        <p class="text-xs text-slate-500">Auth: {{ $api['authentication_type'] }} &bull; Access: {{ $api['access_restrictions'] }}</p>
                    </div>
                    <a href="{{ $api['documentation_url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-emerald-400 hover:underline">Official Docs &rarr;</a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Partner Policies -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800">
            <h2 class="text-base font-semibold text-white">Partner Program Agreements & Governance Policies</h2>
        </div>
        <div class="divide-y divide-slate-800">
            @foreach($policies as $policy)
                <div class="p-5 space-y-2">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-white text-sm">{{ $policy['title'] }}</h3>
                        <a href="{{ $policy['url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-emerald-400 hover:underline">Policy Link &rarr;</a>
                    </div>
                    <ul class="list-disc list-inside text-xs text-slate-400 space-y-1">
                        @foreach($policy['key_clauses'] as $clause)
                            <li>{{ $clause }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
