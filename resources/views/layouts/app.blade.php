<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopify AI Audit Guardian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        guardian: {
                            50: '#f0fdf4',
                            500: '#22c55e',
                            600: '#16a34a',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans antialiased">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50 px-6 py-3.5">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-2xl">🛡️</span>
                <div>
                    <span class="font-bold text-lg text-white tracking-tight">Guardian</span>
                    <span class="text-xs text-emerald-400 font-mono ml-2 px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 rounded">v1.0.0-dev</span>
                </div>
            </div>
            <div class="flex items-center space-x-6 text-sm font-medium">
                <a href="{{ route('guardian.dashboard') }}" class="hover:text-emerald-400 transition-colors {{ request()->routeIs('guardian.dashboard') ? 'text-emerald-400 font-semibold' : 'text-slate-300' }}">Overview</a>
                <a href="{{ route('guardian.findings') }}" class="hover:text-emerald-400 transition-colors {{ request()->routeIs('guardian.findings') ? 'text-emerald-400 font-semibold' : 'text-slate-300' }}">Findings</a>
                <a href="{{ route('guardian.shopify') }}" class="hover:text-emerald-400 transition-colors {{ request()->routeIs('guardian.shopify') ? 'text-emerald-400 font-semibold' : 'text-slate-300' }}">Shopify Intelligence</a>
                <a href="{{ route('guardian.monitoring') }}" class="hover:text-emerald-400 transition-colors {{ request()->routeIs('guardian.monitoring') ? 'text-emerald-400 font-semibold' : 'text-slate-300' }}">Observability</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-6 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-4 px-6 text-center text-xs text-slate-500">
        Shopify AI Audit &bull; Developed by Subhash Ladumor &bull; Audit &bull; Observe &bull; Analyze &bull; Secure &bull; Fix
    </footer>
</body>
</html>
