@extends('guardian::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">All Audit Findings</h1>
            <p class="text-sm text-slate-400 mt-1">Detailed list of discovered vulnerabilities, compliance risks, and remediation advice.</p>
        </div>
        <div class="text-sm font-mono text-slate-400">
            Total: <span class="text-white font-bold">{{ count($findings) }}</span>
        </div>
    </div>

    @if(empty($findings))
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-12 text-center text-slate-400">
            <span class="text-4xl block mb-3">🛡️</span>
            <h2 class="text-lg font-bold text-white">All Clear!</h2>
            <p class="text-sm text-slate-500 mt-1">No findings meet the current severity filter.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($findings as $f)
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-3">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2.5">
                            <span class="font-mono text-xs px-2.5 py-1 rounded border {{ $f->severity->badgeClass() }}">{{ $f->severity->label() }}</span>
                            <span class="font-mono text-xs font-semibold text-slate-300">{{ $f->ruleId }}</span>
                            <span class="text-xs text-slate-500">&bull; {{ $f->category->label() }}</span>
                        </div>
                        <span class="text-xs font-mono text-emerald-400">{{ $f->verificationVerdict->label() }}</span>
                    </div>

                    <h2 class="text-base font-semibold text-white">{{ $f->title }}</h2>
                    <p class="text-sm text-slate-300 leading-relaxed">{{ $f->description }}</p>

                    <div class="bg-slate-950 p-3 rounded-lg border border-slate-800 text-xs font-mono text-slate-400">
                        📁 Location: {{ $f->filePath }}{{ $f->lineStart ? " (Lines {$f->lineStart}-{$f->lineEnd})" : '' }}
                    </div>

                    @if($f->recommendation)
                        <div class="bg-emerald-950/20 border border-emerald-500/20 p-4 rounded-lg">
                            <span class="text-xs uppercase font-bold text-emerald-400 block mb-1">Recommended Solution:</span>
                            <p class="text-xs text-emerald-200">{{ $f->recommendation }}</p>
                        </div>
                    @endif

                    @if($f->suggestedFix)
                        <div class="bg-slate-950 p-4 rounded-lg border border-slate-800">
                            <span class="text-xs uppercase font-bold text-slate-400 block mb-2">Suggested Replacement:</span>
                            <pre class="text-xs font-mono text-emerald-400 overflow-x-auto"><code>{{ $f->suggestedFix }}</code></pre>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
