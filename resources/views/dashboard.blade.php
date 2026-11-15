@extends('guardian::layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header Summary Banner -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 border border-slate-800 rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Security & Compliance Overview</h1>
            <p class="text-sm text-slate-400 mt-1">Audit Run ID: <span class="font-mono text-emerald-400">{{ $audit->auditId }}</span> &bull; Scanned at {{ date('H:i:s Y-m-d') }}</p>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right">
                <div class="text-3xl font-black text-white">{{ round($audit->riskScore->score, 1) }}<span class="text-base text-slate-500 font-normal">/100</span></div>
                <div class="text-xs font-semibold text-emerald-400 uppercase tracking-wide">Grade {{ $audit->riskScore->grade }} &bull; {{ $audit->riskScore->level }} Risk</div>
            </div>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
            <div class="text-xs uppercase font-semibold text-slate-400">Total Findings</div>
            <div class="text-3xl font-extrabold text-white mt-2">{{ count($audit->findings) }}</div>
            <div class="text-xs text-slate-500 mt-1">Across {{ count($audit->scanResults) }} active scanners</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
            <div class="text-xs uppercase font-semibold text-red-400">Critical Vulnerabilities</div>
            <div class="text-3xl font-extrabold text-red-500 mt-2">{{ $audit->criticalCount() }}</div>
            <div class="text-xs text-slate-500 mt-1">Immediate action required</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
            <div class="text-xs uppercase font-semibold text-orange-400">High Severity</div>
            <div class="text-3xl font-extrabold text-orange-400 mt-2">{{ $audit->highCount() }}</div>
            <div class="text-xs text-slate-500 mt-1">Compliance & security issues</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
            <div class="text-xs uppercase font-semibold text-emerald-400">Scan Coverage</div>
            <div class="text-3xl font-extrabold text-emerald-400 mt-2">{{ round($audit->coverage->coveragePercentage, 1) }}%</div>
            <div class="text-xs text-slate-500 mt-1">{{ $audit->coverage->totalFilesScanned }} files verified</div>
        </div>
    </div>

    <!-- Recent Findings Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h2 class="text-base font-semibold text-white">Recent Audit Findings</h2>
            <a href="{{ route('guardian.findings') }}" class="text-xs text-emerald-400 hover:underline">View All Findings &rarr;</a>
        </div>
        @if(empty($audit->findings))
            <div class="p-8 text-center text-slate-400">
                <span class="text-3xl block mb-2">🎉</span>
                <p class="font-medium text-white">No security vulnerabilities detected!</p>
                <p class="text-xs text-slate-500 mt-1">Your codebase conforms to official Shopify and Laravel security standards.</p>
            </div>
        @else
            <div class="divide-y divide-slate-800">
                @foreach(array_slice($audit->findings, 0, 5) as $f)
                    <div class="p-5 hover:bg-slate-800/50 transition-colors flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-xs px-2 py-0.5 rounded border {{ $f->severity->badgeClass() }}">{{ $f->severity->label() }}</span>
                                <span class="font-mono text-xs text-slate-400">{{ $f->ruleId }}</span>
                            </div>
                            <h3 class="font-semibold text-white text-sm">{{ $f->title }}</h3>
                            <p class="text-xs font-mono text-slate-400">{{ $f->filePath }}{{ $f->lineStart ? ":{$f->lineStart}" : '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
