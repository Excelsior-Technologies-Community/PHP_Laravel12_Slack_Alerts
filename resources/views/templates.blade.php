<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Templates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .template-card { transition: all 0.3s; }
        .template-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .badge { font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 500; }
        .modal { transition: all 0.3s; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto p-4 md:p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">📝 Alert Templates</h1>
            <div class="flex gap-3">
                <a href="{{ url('/slack-form') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">← Back</a>
                <button onclick="document.getElementById('createModal').classList.remove('hidden')" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">+ New Template</button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <!-- Templates List -->
        @if($templates->isEmpty())
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                <div class="text-4xl mb-2">📭</div>
                <p>No templates created yet</p>
                <button onclick="document.getElementById('createModal').classList.remove('hidden')" 
                        class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">Create Your First Template</button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($templates as $template)
                    <div class="bg-white rounded-lg shadow p-4 template-card">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold">{{ $template->name }}</h3>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span class="badge bg-purple-100 text-purple-800">{{ ucfirst($template->category) }}</span>
                                    <span class="badge bg-blue-100 text-blue-800">{{ ucfirst($template->priority) }}</span>
                                    <span class="badge {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="badge bg-orange-100 text-orange-800">{{ count($template->placeholders ?? []) }} vars</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ url('/toggle-template/' . $template->id) }}" 
                                   class="text-sm {{ $template->is_active ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800' }}"
                                   onclick="return confirm('Toggle template status?')">
                                    {{ $template->is_active ? '🔇' : '🔊' }}
                                </a>
                                <form method="POST" action="{{ url('/delete-template/' . $template->id) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800" onclick="return confirm('Delete this template?')">🗑️</button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600 bg-gray-50 p-2 rounded">
                            <code class="text-xs">{{ $template->content }}</code>
                        </div>
                        @if($template->placeholders)
                            <div class="mt-2 text-xs text-gray-500">
                                Variables: {{ implode(', ', array_keys($template->placeholders)) }}
                            </div>
                        @endif
                        <div class="mt-2 text-xs text-gray-400">
                            Created: {{ $template->created_at->format('M d, Y') }}
                        </div>
                        <div class="mt-2">
                            <a href="{{ url('/slack-form?template=' . $template->id) }}" 
                               class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded inline-block">Use Template</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Create Modal -->
        <div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Create New Template</h2>
                    <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form method="POST" action="{{ url('/templates') }}">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Template Name *</label>
                            <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g. System Failure Alert">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select name="category" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="info">Info</option>
                                    <option value="alert">Alert</option>
                                    <option value="warning">Warning</option>
                                    <option value="error">Error</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
                                <select name="priority" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Content * <span class="text-xs text-gray-500">(Use {variable} for placeholders)</span></label>
                            <textarea name="content" rows="4" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm" placeholder="🚨 [Danger] System failure on {server_name} at {time}"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Placeholders <span class="text-xs text-gray-500">(JSON format)</span></label>
                            <textarea name="placeholders" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm" placeholder='{"server_name":"Server Name","time":"Time"}'>{"server_name":"Server Name","time":"Time"}</textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="is_active" checked class="w-4 h-4 text-blue-600">
                            <label for="is_active" class="text-sm">Active</label>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition">Create Template</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Navigation -->
        <div class="mt-6 text-center text-sm">
            <a href="{{ url('/dashboard') }}" class="text-blue-500 hover:underline">📊 View Dashboard</a>
            <span class="mx-2 text-gray-300">|</span>
            <a href="{{ url('/messages') }}" class="text-blue-500 hover:underline">📋 View Messages</a>
        </div>
    </div>

    <script>
        // Close modal on outside click
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });

        // Open modal if URL has ?create=true
        if (window.location.search.includes('create=true')) {
            document.getElementById('createModal').classList.remove('hidden');
        }
    </script>
</body>
</html>