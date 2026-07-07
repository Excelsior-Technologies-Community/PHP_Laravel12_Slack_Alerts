<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Slack Message</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .priority-low { border-color: #28a745; background: #d4edda; }
        .priority-normal { border-color: #007bff; background: #cce5ff; }
        .priority-high { border-color: #ffc107; background: #fff3cd; }
        .priority-urgent { border-color: #dc3545; background: #f8d7da; }
        .template-card { cursor: pointer; transition: all 0.3s; }
        .template-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .template-card.active { border: 2px solid #4a90e2; background: #e3f2fd; }
        .placeholder-input { border: 1px dashed #4a90e2; background: #f8f9fa; }
        .stat-card { transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-4 md:p-6">
        
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="stat-card bg-white rounded-lg p-4 text-center shadow">
                <div class="text-2xl font-bold text-blue-500">{{ $stats['total'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">Total Messages</div>
            </div>
            <div class="stat-card bg-white rounded-lg p-4 text-center shadow">
                <div class="text-2xl font-bold text-green-500">{{ $stats['today'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">Today</div>
            </div>
            <div class="stat-card bg-white rounded-lg p-4 text-center shadow">
                <div class="text-2xl font-bold text-red-500">{{ $stats['urgent'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">Urgent</div>
            </div>
            <div class="stat-card bg-white rounded-lg p-4 text-center shadow border-2 border-red-300">
                <div class="text-2xl font-bold text-orange-500">{{ $stats['failed'] ?? 0 }}</div>
                <div class="text-xs text-red-500">Failed</div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">📨 Send Slack Message</h1>
                <div class="flex gap-2">
                    <a href="{{ url('/messages') }}" class="text-sm text-blue-500 hover:underline">📋 Messages</a>
                    <a href="{{ url('/dashboard') }}" class="text-sm text-purple-500 hover:underline">📊 Dashboard</a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ url('/send-message') }}" id="messageForm">
                @csrf

                <!-- Template Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">📋 Use Template (Optional)</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="templateList">
                        <div class="template-card border rounded-lg p-3" data-template-id="" onclick="selectTemplate(this)">
                            <div class="font-semibold text-sm">✏️ Custom Message</div>
                            <div class="text-xs text-gray-500">Write your own message</div>
                        </div>
                        @foreach($templates ?? [] as $template)
                            <div class="template-card border rounded-lg p-3" data-template-id="{{ $template->id }}" 
                                 data-content="{{ $template->content }}" data-placeholders="{{ json_encode($template->placeholders) }}"
                                 onclick="selectTemplate(this)">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-semibold text-sm">{{ $template->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $template->category }} • {{ $template->priority }}</div>
                                    </div>
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">{{ count($template->placeholders ?? []) }} vars</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="template_id" id="selectedTemplateId" value="">
                </div>

                <!-- Placeholders -->
                <div id="placeholderContainer" class="hidden mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">🔤 Template Variables</label>
                    <div id="placeholderInputs" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
                </div>

                <div class="form-group mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                    <textarea id="message" name="message" rows="4" maxlength="500" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter your message here..." required></textarea>
                    <div class="text-right text-xs text-gray-500 mt-1">
                        <span id="charCount">0</span>/500
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['low', 'normal', 'high', 'urgent'] as $p)
                                <label class="priority-{{ $p }} border-2 rounded-lg p-2 text-center cursor-pointer transition">
                                    <input type="radio" name="priority" value="{{ $p }}" {{ $p == 'normal' ? 'checked' : '' }} class="hidden">
                                    <span class="text-sm capitalize">{{ $p }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['info', 'alert', 'warning', 'error'] as $c)
                                <label class="border-2 rounded-lg p-2 text-center cursor-pointer transition hover:border-blue-400">
                                    <input type="radio" name="category" value="{{ $c }}" {{ $c == 'info' ? 'checked' : '' }} class="hidden">
                                    <span class="text-sm capitalize">{{ $c }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sender Name</label>
                        <input type="text" name="sender_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Your name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sender Email</label>
                        <input type="email" name="sender_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="your@email.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Schedule for later</label>
                    <input type="datetime-local" name="schedule_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition">
                    📤 Send to Slack
                </button>
            </form>
        </div>

        <!-- Navigation -->
        <div class="flex justify-center gap-4 text-sm">
            <a href="{{ url('/messages') }}" class="text-blue-500 hover:underline">📋 View Messages</a>
            <a href="{{ url('/dashboard') }}" class="text-blue-500 hover:underline">📊 Dashboard</a>
            <a href="{{ url('/templates') }}" class="text-blue-500 hover:underline">📝 Templates</a>
        </div>
    </div>

    <script>
        // Character counter
        const messageInput = document.getElementById('message');
        const charCount = document.getElementById('charCount');
        messageInput.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });

        // Template selection
        function selectTemplate(el) {
            document.querySelectorAll('.template-card').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            
            const templateId = el.dataset.templateId;
            document.getElementById('selectedTemplateId').value = templateId;
            
            if (templateId) {
                const content = el.dataset.content;
                const placeholders = JSON.parse(el.dataset.placeholders || '{}');
                document.getElementById('message').value = content;
                charCount.textContent = content.length;
                
                // Show placeholder inputs
                const container = document.getElementById('placeholderContainer');
                const inputs = document.getElementById('placeholderInputs');
                inputs.innerHTML = '';
                
                if (Object.keys(placeholders).length > 0) {
                    container.classList.remove('hidden');
                    Object.entries(placeholders).forEach(([key, label]) => {
                        inputs.innerHTML += `
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">${label}</label>
                                <input type="text" name="placeholders[${key}]" placeholder="Enter ${label.toLowerCase()}" 
                                    class="placeholder-input w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                                    oninput="updateTemplatePreview()">
                            </div>
                        `;
                    });
                } else {
                    container.classList.add('hidden');
                }
            } else {
                document.getElementById('placeholderContainer').classList.add('hidden');
                document.getElementById('message').value = '';
                charCount.textContent = '0';
            }
        }

        function updateTemplatePreview() {
            const templateId = document.getElementById('selectedTemplateId').value;
            if (!templateId) return;
            
            const el = document.querySelector(`.template-card[data-template-id="${templateId}"]`);
            if (!el) return;
            
            let content = el.dataset.content;
            const inputs = document.querySelectorAll('#placeholderInputs input');
            inputs.forEach(input => {
                const key = input.name.replace('placeholders[', '').replace(']', '');
                content = content.replace(`{${key}}`, input.value || `{${key}}`);
            });
            
            document.getElementById('message').value = content;
            charCount.textContent = content.length;
        }

        // Priority selection
        document.querySelectorAll('.priority-low, .priority-normal, .priority-high, .priority-urgent').forEach(el => {
            el.addEventListener('click', function() {
                document.querySelectorAll('.priority-low, .priority-normal, .priority-high, .priority-urgent')
                    .forEach(e => e.style.borderColor = '#d1d5db');
                this.style.borderColor = '#4a90e2';
                this.querySelector('input').checked = true;
            });
        });

        // Category selection
        document.querySelectorAll('[name="category"]').forEach(el => {
            el.closest('label').addEventListener('click', function() {
                document.querySelectorAll('[name="category"]').forEach(e => {
                    e.closest('label').style.borderColor = '#d1d5db';
                });
                this.style.borderColor = '#4a90e2';
                this.querySelector('input').checked = true;
            });
        });

        // Set default category
        document.querySelector('[name="category"][value="info"]')?.closest('label')?.click();
    </script>
</body>
</html>