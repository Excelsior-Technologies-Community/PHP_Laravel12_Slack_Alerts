<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Messages</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .status-sent { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }
        .retry-btn { transition: all 0.3s; }
        .retry-btn:hover { transform: scale(1.05); }
        .message-card { transition: all 0.3s; }
        .message-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .failed-card { border-left: 4px solid #dc3545; }
        .badge { font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 500; }
        .action-btn { transition: all 0.2s; }
        .action-btn:hover { transform: scale(1.05); }
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 14px; background: white; border-radius: 8px; text-decoration: none; color: #4a90e2; border: 1px solid #e5e7eb; }
        .pagination .active { background: #4a90e2; color: white; border-color: #4a90e2; }
        .pagination a:hover { background: #f3f4f6; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto p-4 md:p-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">📋 Saved Messages</h1>
            <div class="flex gap-2">
                <a href="{{ url('/slack-form') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">← Back</a>
                <a href="{{ url('/dashboard') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">📊 Dashboard</a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
            <div class="bg-white rounded-lg p-3 text-center shadow-sm"><div class="text-xl font-bold text-blue-500">{{ $stats['total'] ?? 0 }}</div><div class="text-xs text-gray-500">Total</div></div>
            <div class="bg-white rounded-lg p-3 text-center shadow-sm"><div class="text-xl font-bold text-green-500">{{ $stats['today'] ?? 0 }}</div><div class="text-xs text-gray-500">Today</div></div>
            <div class="bg-white rounded-lg p-3 text-center shadow-sm"><div class="text-xl font-bold text-red-500">{{ $stats['urgent'] ?? 0 }}</div><div class="text-xs text-gray-500">Urgent</div></div>
            <div class="bg-white rounded-lg p-3 text-center shadow-sm border-2 border-red-300"><div class="text-xl font-bold text-red-600">{{ $stats['failed'] ?? 0 }}</div><div class="text-xs text-red-500">⚠️ Failed</div></div>
            <div class="bg-white rounded-lg p-3 text-center shadow-sm border-2 border-yellow-300"><div class="text-xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</div><div class="text-xs text-yellow-500">⏳ Pending</div></div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <input type="text" name="search" placeholder="Search..." value="{{ $search ?? '' }}" class="px-3 py-2 border rounded-lg text-sm">
                <select name="priority" class="px-3 py-2 border rounded-lg text-sm">
                    <option value="all">All Priorities</option>
                    <option value="low" {{ ($priority ?? '') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ ($priority ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ ($priority ?? '') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ ($priority ?? '') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
                <select name="status" class="px-3 py-2 border rounded-lg text-sm">
                    <option value="all">All Status</option>
                    <option value="sent" {{ ($status ?? '') == 'sent' ? 'selected' : '' }}>✅ Sent</option>
                    <option value="failed" {{ ($status ?? '') == 'failed' ? 'selected' : '' }}>❌ Failed</option>
                    <option value="pending" {{ ($status ?? '') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex-1">Filter</button>
                    <a href="{{ url('/messages') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">Reset</a>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div class="bg-white rounded-lg shadow p-3 mb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <input type="checkbox" id="selectAll" onchange="toggleAll()" class="w-4 h-4">
                <label for="selectAll" class="text-sm">Select All</label>
                <button onclick="deleteSelected()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded-lg text-sm action-btn">🗑️ Delete Selected</button>
            </div>
            <div class="flex gap-2">
                <a href="{{ url('/export-messages') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-sm action-btn">📥 Export CSV</a>
                <a href="{{ url('/templates') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-1.5 rounded-lg text-sm action-btn">📝 Templates</a>
            </div>
        </div>

        <!-- Messages -->
        @if($messages->isEmpty())
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                <div class="text-4xl mb-2">📭</div>
                <p>No messages found</p>
                <a href="{{ url('/slack-form') }}" class="text-blue-500 hover:underline text-sm mt-2 inline-block">Send your first message →</a>
            </div>
        @else
            <form id="bulkDeleteForm" method="POST" action="{{ url('/bulk-delete') }}">
                @csrf
                @method('DELETE')
                
                @foreach($messages as $msg)
                    <div class="bg-white rounded-lg shadow p-4 mb-3 message-card {{ !$msg->is_sent && $msg->failed_at ? 'failed-card border-l-4 border-red-500' : '' }}">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="message_ids[]" value="{{ $msg->id }}" class="mt-1 message-checkbox w-4 h-4">
                            
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="badge bg-blue-100 text-blue-800">Priority: {{ ucfirst($msg->priority) }}</span>
                                    <span class="badge bg-purple-100 text-purple-800">{{ ucfirst($msg->category) }}</span>
                                    
                                    @if($msg->is_sent)
                                        <span class="badge bg-green-100 text-green-800">✅ Sent</span>
                                    @elseif($msg->failed_at)
                                        <span class="badge bg-red-100 text-red-800">❌ Failed</span>
                                        @if($msg->retry_count > 0)
                                            <span class="badge bg-orange-100 text-orange-800">Retry #{{ $msg->retry_count }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-yellow-100 text-yellow-800">⏳ Pending</span>
                                    @endif
                                    
                                    @if($msg->template_id)
                                        <span class="badge bg-indigo-100 text-indigo-800">📝 Template</span>
                                    @endif
                                </div>

                                <div class="text-gray-800 mb-2">{{ $msg->message }}</div>

                                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                    @if($msg->sender_name)<span>👤 {{ $msg->sender_name }}</span>@endif
                                    @if($msg->sender_email)<span>📧 {{ $msg->sender_email }}</span>@endif
                                    <span>🕐 {{ $msg->created_at->format('M d, Y h:i A') }}</span>
                                    @if($msg->failed_at)
                                        <span class="text-red-500">Failed: {{ $msg->failed_at->format('M d, Y h:i A') }}</span>
                                    @endif
                                    @if($msg->failure_reason)
                                        <span class="text-red-400 text-xs">Reason: {{ $msg->failure_reason }}</span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2 mt-3">
                                    <!-- RETRY BUTTON - Only show for failed messages -->
                                    @if(!$msg->is_sent && $msg->failed_at && $msg->retry_count < 3)
                                        <a href="{{ url('/resend-message/' . $msg->id) }}" 
                                           class="retry-btn bg-orange-500 hover:bg-orange-600 text-white px-4 py-1.5 rounded-lg text-sm inline-flex items-center gap-1 action-btn"
                                           onclick="return confirm('Retry sending this message? (Attempt {{ $msg->retry_count + 1 }}/3)')">
                                            🔄 Retry Now
                                        </a>
                                    @elseif(!$msg->is_sent && $msg->failed_at && $msg->retry_count >= 3)
                                        <span class="text-red-500 text-sm bg-red-50 px-3 py-1 rounded">Max retries exceeded</span>
                                    @endif

                                    <!-- Resend Button (for sent messages) -->
                                    @if($msg->is_sent)
                                        <a href="{{ url('/resend-message/' . $msg->id) }}" 
                                           class="bg-green-500 hover:bg-green-600 text-white px-4 py-1.5 rounded-lg text-sm action-btn"
                                           onclick="return confirm('Resend this message?')">
                                            📤 Resend
                                        </a>
                                    @endif

                                    <!-- DELETE BUTTON - FIXED with form -->
                                    <form method="POST" action="{{ url('/delete-message/' . $msg->id) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-lg text-sm action-btn"
                                                onclick="return confirm('Delete this message?')">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </form>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $messages->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <script>
        function toggleAll() {
            const checked = document.getElementById('selectAll').checked;
            document.querySelectorAll('.message-checkbox').forEach(cb => cb.checked = checked);
        }

        function deleteSelected() {
            const selected = document.querySelectorAll('.message-checkbox:checked');
            if (selected.length === 0) {
                alert('Please select at least one message');
                return;
            }
            if (confirm('Delete ' + selected.length + ' messages?')) {
                document.getElementById('bulkDeleteForm').submit();
            }
        }

        // Auto-refresh failed messages count (optional)
        setInterval(() => {
            fetch('{{ url("/messages?status=failed") }}')
                .then(response => response.text())
                .then(html => {
                    const count = (html.match(/Failed/g) || []).length;
                    document.querySelector('.text-red-600')?.textContent = count;
                })
                .catch(() => {});
        }, 30000);
    </script>
</body>
</html>