<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Messages</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        h1 {
            color: #333;
            font-size: 24px;
        }

        .back-btn {
            background: #4a90e2;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #357abd;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #4a90e2;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        /* Filters Section */
        .filters-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 500;
            color: #666;
        }

        .filter-group input,
        .filter-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #4a90e2;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-primary {
            padding: 8px 16px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-secondary {
            padding: 8px 16px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        /* Message Cards */
        .message-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-priority-low { background: #d4edda; color: #155724; }
        .badge-priority-normal { background: #cce5ff; color: #004085; }
        .badge-priority-high { background: #fff3cd; color: #856404; }
        .badge-priority-urgent { background: #f8d7da; color: #721c24; }

        .badge-category { background: #e2e3e5; color: #383d41; }

        .message-text {
            color: #333;
            font-size: 16px;
            margin: 15px 0;
            line-height: 1.5;
        }

        .message-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
        }

        .message-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }

        .action-resend {
            background: #28a745;
            color: white;
        }

        .action-delete {
            background: #dc3545;
            color: white;
        }

        .checkbox {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            background: white;
            border-radius: 6px;
            text-decoration: none;
            color: #4a90e2;
        }

        .pagination .active {
            background: #4a90e2;
            color: white;
        }

        /* Bulk Actions */
        .bulk-actions {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1> Saved Messages</h1>
            <a href="{{ url('/slack-form') }}" class="back-btn">← Back to Form</a>
        </div>

       

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="{{ url('/messages') }}" class="filters-form">
                <div class="filter-group">
                    <label> Search</label>
                    <input type="text" name="search" placeholder="Search messages..." value="{{ $search ?? '' }}">
                </div>
                
                <div class="filter-group">
                    <label> Priority</label>
                    <select name="priority">
                        <option value="all">All Priorities</option>
                        <option value="low" {{ ($priority ?? '') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ ($priority ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ ($priority ?? '') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ ($priority ?? '') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label> Category</label>
                    <select name="category">
                        <option value="all">All Categories</option>
                        <option value="info" {{ ($category ?? '') == 'info' ? 'selected' : '' }}>Info</option>
                        <option value="alert" {{ ($category ?? '') == 'alert' ? 'selected' : '' }}>Alert</option>
                        <option value="warning" {{ ($category ?? '') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="error" {{ ($category ?? '') == 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label> From Date</label>
                    <input type="date" name="date_from" value="{{ $date_from ?? '' }}">
                </div>
                
                <div class="filter-group">
                    <label> To Date</label>
                    <input type="date" name="date_to" value="{{ $date_to ?? '' }}">
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn-primary">Apply Filters</button>
                    <a href="{{ url('/messages') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <span>Bulk Actions:</span>
            <button onclick="deleteSelected()" class="action-btn action-delete">Delete Selected</button>
        </div>

        @if($messages->isEmpty())
            <div class="stat-card" style="text-align: center; padding: 40px;">
                <p> No messages found</p>
                <p style="font-size: 12px; margin-top: 10px;">Send your first message using the form</p>
            </div>
        @else
            <form id="bulkDeleteForm" method="POST" action="{{ url('/bulk-delete') }}">
                @csrf
                @method('DELETE')
                
                @foreach($messages as $msg)
                    <div class="message-card">
                        <input type="checkbox" name="message_ids[]" value="{{ $msg->id }}" class="checkbox" onchange="updateBulkButton()">
                        
                        <div class="message-header">
                            <div>
                                <span class="badge badge-priority-{{ $msg->priority }}">{{ ucfirst($msg->priority) }}</span>
                                <span class="badge badge-category">{{ ucfirst($msg->category) }}</span>
                            </div>
                            <div class="message-actions">
                                <a href="{{ url('/resend-message/' . $msg->id) }}" class="action-btn action-resend" onclick="return confirm('Resend to Slack?')">📤 Resend</a>
                                <a href="{{ url('/delete-message/' . $msg->id) }}" class="action-btn action-delete" onclick="return confirm('Delete this message?')">🗑️ Delete</a>
                            </div>
                        </div>
                        
                        <div class="message-text">
                            @if($search)
                                {!! str_replace($search, "<span class='highlight'>{$search}</span>", e($msg->message)) !!}
                            @else
                                {{ $msg->message }}
                            @endif
                        </div>
                        
                        <div class="message-meta">
                            @if($msg->sender_name)
                                <span> {{ $msg->sender_name }}</span>
                            @endif
                            @if($msg->sender_email)
                                <span> {{ $msg->sender_email }}</span>
                            @endif
                            <span> {{ $msg->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                @endforeach
            </form>
            
            <!-- Pagination -->
            <div class="pagination">
                {{ $messages->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <script>
        function deleteSelected() {
            var checkboxes = document.querySelectorAll('.checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Please select at least one message to delete');
                return;
            }
            if (confirm('Delete ' + checkboxes.length + ' messages?')) {
                document.getElementById('bulkDeleteForm').submit();
            }
        }
        
        function updateBulkButton() {
            var checkboxes = document.querySelectorAll('.checkbox:checked');
            var btn = document.querySelector('.bulk-actions button');
            if (btn) {
                btn.textContent = checkboxes.length > 0 ? 'Delete Selected (' + checkboxes.length + ')' : 'Delete Selected';
            }
        }
    </script>
</body>
</html>