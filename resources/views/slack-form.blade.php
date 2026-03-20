<!DOCTYPE html>
<html>

<head>
    <title>Slack Alert Form</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #1e293b;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            margin-bottom: 15px;
            background: #334155;
            color: white;
        }

        input::placeholder {
            color: #94a3b8;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #22c55e;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #16a34a;
        }

        .success {
            background: #16a34a;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 10px;
        }

        .nav {
            text-align: center;
            margin-top: 15px;
        }

        .nav a {
            color: #38bdf8;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="card">
        <h2>🚀 Send Slack Message</h2>

        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="/send-message">
            @csrf
            <input type="text" name="message" placeholder="Enter your message..." required>
            <button type="submit">Send to Slack</button>
        </form>

        <div class="nav">
            <a href="/messages">📜 View Messages</a>
        </div>
    </div>

</body>

</html>