<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Slack Message</title>
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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #4a90e2;
        }

        .priority-buttons,
        .category-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .priority-option,
        .category-option {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .priority-option input,
        .category-option input {
            display: none;
        }

        .priority-option label,
        .category-option label {
            margin: 0;
            cursor: pointer;
            display: block;
        }

        .priority-option.selected {
            border-color: #4a90e2;
            background: #e3f2fd;
        }

        .priority-low.selected {
            border-color: #28a745;
            background: #d4edda;
        }

        .priority-normal.selected {
            border-color: #007bff;
            background: #cce5ff;
        }

        .priority-high.selected {
            border-color: #ffc107;
            background: #fff3cd;
        }

        .priority-urgent.selected {
            border-color: #dc3545;
            background: #f8d7da;
        }

        .category-option.selected {
            border-color: #4a90e2;
            background: #e3f2fd;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #357abd;
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

        .nav-links {
            margin-top: 20px;
            text-align: center;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #4a90e2;
            text-decoration: none;
            font-size: 14px;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #eee;
        }

        /* NEW: Character counter + preview box */

        .char-counter {
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .preview-box {
            margin-top: 20px;
            background: #f8f9fa;
            border-left: 4px solid #4a90e2;
            padding: 15px;
            border-radius: 8px;
        }

        .preview-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .preview-content {
            color: #555;
            min-height: 40px;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <div class="container">


        <div class="card">
            <h1> Send Slack Message</h1>


            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ url('/send-message') }}">
                @csrf

                <div class="form-group">
                    <label for="message">Message *</label>

                    <textarea
                        id="message"
                        name="message"
                        maxlength="500"
                        placeholder="Enter your message here..."
                        required></textarea>

                    <div class="char-counter">
                        <span id="charCount">0</span>/500
                    </div>
                </div>

                <div class="form-group">
                    <label>Priority *</label>
                    <div class="priority-buttons">
                        <div class="priority-option priority-low" data-value="low">
                            <input type="radio" name="priority" value="low" id="priority_low">
                            <label for="priority_low"> Low</label>
                        </div>
                        <div class="priority-option priority-normal" data-value="normal">
                            <input type="radio" name="priority" value="normal" id="priority_normal" checked>
                            <label for="priority_normal"> Normal</label>
                        </div>
                        <div class="priority-option priority-high" data-value="high">
                            <input type="radio" name="priority" value="high" id="priority_high">
                            <label for="priority_high"> High</label>
                        </div>
                        <div class="priority-option priority-urgent" data-value="urgent">
                            <input type="radio" name="priority" value="urgent" id="priority_urgent">
                            <label for="priority_urgent"> Urgent</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <div class="category-buttons">
                        <div class="category-option" data-value="info">
                            <input type="radio" name="category" value="info" id="category_info" checked>
                            <label for="category_info"> Info</label>
                        </div>
                        <div class="category-option" data-value="alert">
                            <input type="radio" name="category" value="alert" id="category_alert">
                            <label for="category_alert"> Alert</label>
                        </div>
                        <div class="category-option" data-value="warning">
                            <input type="radio" name="category" value="warning" id="category_warning">
                            <label for="category_warning"> Warning</label>
                        </div>
                        <div class="category-option" data-value="error">
                            <input type="radio" name="category" value="error" id="category_error">
                            <label for="category_error"> Error</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sender_name">Sender Name (Optional)</label>
                    <input type="text" id="sender_name" name="sender_name" placeholder="Your name">
                </div>

                <div class="form-group">
                    <label for="sender_email">Sender Email (Optional)</label>
                    <input type="email" id="sender_email" name="sender_email" placeholder="your@email.com">
                </div>

                <div class="form-group">
                    <label for="schedule_date">Schedule for later (Optional)</label>
                    <input type="date" id="schedule_date" name="schedule_date">
                </div>

                <button type="submit">Send to Slack</button>
            </form>

            <div class="preview-box">

                <div class="preview-title">
                    Live Preview
                </div>

                <div id="previewText" class="preview-content">
                    Your message preview appears here...
                </div>

            </div>

            <hr>

            <div class="nav-links">
                <a href="{{ url('/messages') }}"> View Messages</a>

                <a href="{{ url('/export-messages') }}"> Export CSV</a>
            </div>
        </div>
    </div>

    <script>
        // Priority selection
        document.querySelectorAll('.priority-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.priority-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
            });
        });

        // Category selection
        document.querySelectorAll('.category-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.category-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
            });
        });

        // Set default selected
        document.querySelector('.priority-option[data-value="normal"]').classList.add('selected');
        document.querySelector('.category-option[data-value="info"]').classList.add('selected');

        // Character counter + live preview

        const textarea = document.getElementById('message');
        const counter = document.getElementById('charCount');
        const preview = document.getElementById('previewText');

        textarea.addEventListener('input', function() {

            counter.innerText = this.value.length;

            preview.innerText =
                this.value || "Your message preview appears here...";

        }); 
    </script>
</body>

</html>