<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Slack Alerts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .stat-card { transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .chart-bar { transition: all 0.5s; }
        .chart-bar:hover { opacity: 0.8; }
        .badge { font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 500; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto p-4 md:p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">📊 Dashboard</h1>
                <p class="text-sm text-gray-500">Overview of all Slack messages and templates</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ url('/slack-form') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">← Back</a>
                <a href="{{ url('/messages') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">📋 Messages</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card bg-white rounded-xl shadow p-4 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $totalMessages ?? 0 }}</div>
                <div class="text-sm text-gray-500 mt-1">📨 Total Messages</div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow p-4 text-center">
                <div class="text-3xl font-bold text-green-600">{{ ($totalMessages ?? 0) - ($failedMessages ?? 0) - ($pendingMessages ?? 0) }}</div>
                <div class="text-sm text-gray-500 mt-1">✅ Sent</div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow p-4 text-center border-2 border-red-300">
                <div class="text-3xl font-bold text-red-600">{{ $failedMessages ?? 0 }}</div>
                <div class="text-sm text-red-500 mt-1">❌ Failed</div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow p-4 text-center border-2 border-yellow-300">
                <div class="text-3xl font-bold text-yellow-600">{{ $pendingMessages ?? 0 }}</div>
                <div class="text-sm text-yellow-500 mt-1">⏳ Pending</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Messages by Priority -->
            <div class="bg-white rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-700 mb-3">🎯 Messages by Priority</h3>
                <div class="space-y-3">
                    @php
                        $priorities = ['urgent' => 'red', 'high' => 'orange', 'normal' => 'blue', 'low' => 'green'];
                        $total = array_sum($messagesByPriority->toArray()) ?: 1;
                    @endphp
                    @foreach($priorities as $key => $color)
                        @php
                            $count = $messagesByPriority[$key] ?? 0;
                            $percentage = round(($count / $total) * 100);
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="capitalize">{{ $key }}</span>
                                <span>{{ $count }} ({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-{{ $color }}-500 h-2.5 rounded-full chart-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Messages by Category -->
            <div class="bg-white rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-700 mb-3">📂 Messages by Category</h3>
                <div class="space-y-3">
                    @php
                        $categories = ['error' => 'red', 'warning' => 'yellow', 'alert' => 'orange', 'info' => 'blue'];
                        $total = array_sum($messagesByCategory->toArray()) ?: 1;
                    @endphp
                    @foreach($categories as $key => $color)
                        @php
                            $count = $messagesByCategory[$key] ?? 0;
                            $percentage = round(($count / $total) * 100);
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="capitalize">{{ $key }}</span>
                                <span>{{ $count }} ({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-{{ $color }}-500 h-2.5 rounded-full chart-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Charts - Last 7 Days -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-700 mb-3">📈 Messages Last 7 Days</h3>
                <div class="flex items-end h-48 gap-2">
                    @php
                        $max = $last7Days->max('count') ?: 1;
                    @endphp
                    @foreach($last7Days as $day)
                        @php
                            $height = ($day->count / $max) * 100;
                            $date = \Carbon\Carbon::parse($day->date);
                        @endphp
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-blue-500 rounded-t hover:bg-blue-600 transition" 
                                 style="height: {{ max(5, $height) }}px; min-height: 10px;"></div>
                            <div class="text-xs text-gray-500 mt-1">{{ $date->format('D') }}</div>
                            <div class="text-xs font-bold">{{ $day->count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-700 mb-3">❌ Failed Messages Last 7 Days</h3>
                <div class="flex items-end h-48 gap-2">
                    @php
                        $max = $failedByDay->max('count') ?: 1;
                    @endphp
                    @foreach($failedByDay as $day)
                        @php
                            $height = ($day->count / $max) * 100;
                            $date = \Carbon\Carbon::parse($day->date);
                        @endphp
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-red-500 rounded-t hover:bg-red-600 transition" 
                                 style="height: {{ max(5, $height) }}px; min-height: 10px;"></div>
                            <div class="text-xs text-gray-500 mt-1">{{ $date->format('D') }}</div>
                            <div class="text-xs font-bold">{{ $day->count }}</div>
                        </div>
                    @endforeach
                </div>
                @if($failedByDay->isEmpty())
                    <div class="text-center text-gray-400 text-sm mt-4">✅ No failed messages in last 7 days</div>
                @endif
            </div>
        </div>

        <!-- Templates List -->
        <div class="bg-white rounded-xl shadow p-5 mb-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-700">📝 Active Templates</h3>
                <a href="{{ url('/templates') }}" class="text-sm text-blue-500 hover:underline">View All →</a>
            </div>
            @if($templates->isEmpty())
                <div class="text-center text-gray-400 py-4">
                    <p>No templates created yet</p>
                    <a href="{{ url('/templates') }}" class="text-blue-500 hover:underline text-sm">Create your first template →</a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($templates as $template)
                        <div class="border rounded-lg p-3 hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-medium text-sm">{{ $template->name }}</div>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <span class="badge bg-purple-100 text-purple-800">{{ ucfirst($template->category) }}</span>
                                        <span class="badge bg-blue-100 text-blue-800">{{ ucfirst($template->priority) }}</span>
                                        <span class="badge bg-green-100 text-green-800">{{ count($template->placeholders ?? []) }} vars</span>
                                    </div>
                                </div>
                                <a href="{{ url('/slack-form?template=' . $template->id) }}" 
                                   class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">Use</a>
                            </div>
                            <div class="text-xs text-gray-500 mt-2 truncate">{{ $template->content }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ url('/slack-form') }}" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow p-4 text-center transition">
                <div class="text-2xl mb-1">📨</div>
                <div class="text-sm font-medium">New Message</div>
            </a>
            <a href="{{ url('/messages') }}" class="bg-purple-600 hover:bg-purple-700 text-white rounded-xl shadow p-4 text-center transition">
                <div class="text-2xl mb-1">📋</div>
                <div class="text-sm font-medium">View Messages</div>
            </a>
            <a href="{{ url('/templates') }}" class="bg-green-600 hover:bg-green-700 text-white rounded-xl shadow p-4 text-center transition">
                <div class="text-2xl mb-1">📝</div>
                <div class="text-sm font-medium">Templates</div>
            </a>
            <a href="{{ url('/export-messages') }}" class="bg-orange-600 hover:bg-orange-700 text-white rounded-xl shadow p-4 text-center transition">
                <div class="text-2xl mb-1">📥</div>
                <div class="text-sm font-medium">Export CSV</div>
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-gray-400 mt-8 border-t border-gray-200 pt-4">
            Slack Alerts Dashboard • {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <script>
        // Auto-refresh stats every 30 seconds
        setInterval(() => {
            fetch('{{ url("/dashboard") }}')
                .then(response => response.text())
                .then(html => {
                    // Simple refresh - just reload the page
                    // Or you can use AJAX to update specific elements
                })
                .catch(() => {});
        }, 30000);

        console.log('📊 Dashboard loaded successfully!');
    </script>
</body>
</html>