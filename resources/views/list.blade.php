<!DOCTYPE html>
<html>

<head>
    <title>Saved Messages</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a, #020617);
            color: #e2e8f0;
            min-height: 100vh;
            padding: 40px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            letter-spacing: 1px;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        /* 🔹 Glass Card */
        .card {
            backdrop-filter: blur(12px);
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        /* Hover Glow */
        .card:hover {
            transform: translateY(-5px) scale(1.02);
            border-color: #38bdf8;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);
        }

        /* Animated Border */
        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 15px;
            padding: 1px;
            background: linear-gradient(120deg, #38bdf8, #22c55e, #a855f7);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: 0.4s;
        }

        .card:hover::before {
            opacity: 1;
        }

        .message {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .date {
            font-size: 13px;
            color: #94a3b8;
        }

        /* 🔹 Top Navigation */
        .top-nav {
            text-align: center;
            margin-bottom: 25px;
        }

        .top-nav a {
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            background: linear-gradient(135deg, #38bdf8, #22c55e);
            color: white;
            font-weight: bold;
            transition: 0.3s;
        }

        .top-nav a:hover {
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.6);
        }

        /* 🔹 Empty State */
        .empty {
            text-align: center;
            color: #64748b;
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="top-nav"> <a href="/slack-form">⬅ Back to Form</a> </div>
        <h2>📜 Saved Slack Messages</h2> @if($messages->isEmpty())
        <div class="empty"> 🚫 No messages found </div> @endif @foreach($messages as $msg) <div class="card">
            <div class="message">💬 {{ $msg->message }}</div>
            <div class="date">🕒 {{ $msg->created_at->format('d M Y, h:i A') }}</div>
        </div> @endforeach
    </div>
</body>

</html>