#  PHP_Laravel12_Slack_Alerts 


## Project Description

PHP_Laravel12_Slack_Alerts is a Laravel 12 web application that demonstrates how to integrate Slack notifications into a Laravel project using the Spatie Slack Alerts package.

The application allows users to send messages through a simple web form, which are then:

Sent directly to a Slack channel using a webhook

Stored in a MySQL database for record keeping

This project is useful for learning real-time notifications, third-party API integration, and database handling in Laravel.


## Features

- Send messages directly to a Slack channel using webhook

- Stored in a MySQL database for record keeping  

- View all sent messages

- Modern Dark UI with card design

- Form validation for secure input

- Real-time Slack notification using webhook

- Timestamp for each message



## Use Case

This project can be used for:

- System alert notifications  
- Admin notifications  
- Error reporting to Slack  
- Activity logging systems  



## Technologies Used

1. PHP 8.x – Backend programming language

2. Laravel 12 – PHP framework

3. MySQL – Database

4. Spatie Laravel Slack Alerts – Slack integration package

5. Blade Template Engine – Frontend rendering

6. HTML + CSS – UI design



---



## Installation Steps


---


## STEP 1: Create Laravel 12 Project

### Open terminal / CMD and run:

```
composer create-project laravel/laravel PHP_Laravel12_Slack_Alerts "12.*"

```

### Go inside project:

```
cd PHP_Laravel12_Slack_Alerts

```

#### Explanation:

Creates a new Laravel 12 project with all required dependencies.

Moves into the project directory to start development.




## STEP 2: Database Setup 

### Update database details:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_Slack_Alerts
DB_USERNAME=root
DB_PASSWORD=

```

### Create database in MySQL / phpMyAdmin:

```
Database name: laravel12_Slack_Alerts

```

### Then Run:

```
php artisan migrate

```


#### Explanation:

Connects Laravel to MySQL database and creates default tables like users, sessions, etc.




## STEP 3: Install Slack Package 

### Run:

```
composer require spatie/laravel-slack-alerts

```

#### Explanation:

Installs the Slack Alerts package to send messages from Laravel to Slack.





## STEP 4: Publish Configuration

### Run:

```
php artisan vendor:publish --provider="Spatie\SlackAlerts\SlackAlertsServiceProvider"

```

#### Explanation:

Publishes the package config file so you can customize Slack settings.





## STEP 5: Configure Environment

### STEP 5.1: Open Correct Slack App Dashboard

#### Go here:

```
https://api.slack.com/apps

```

#### Now:

1. Click Create New App

2. Choose From scratch

3. Fill:

- App Name: Laravel Slack Alerts

- Workspace: select your workspace

4. Click Create App



### STEP 5.2: Now do this:

1. Click Incoming Webhooks (left sidebar)

2. Turn ON:

```
Activate Incoming Webhooks = ON

```



### STEP 5.3: Generate Webhook URL

#### Now:

1. Scroll down

2. Click:

```
Add New Webhook to Workspace

```

3. Select channel (example: #general)

4. Click Allow

###  You will now get:

```
https://hooks.slack.com/services/TXXXXX/BXXXXX/XXXXXXXXXX

```


### STEP 5.4: Update .env and Clear Cache

#### Open .env file and add:

```
SLACK_ALERT_WEBHOOK=https://hooks.slack.com/services/XXXXX/XXXXX/XXXXX

```

#### Run: 

```
php artisan config:clear
php artisan cache:clear
php artisan config:cache

```

#### Explanation:

Stores your Slack webhook URL and refreshes configuration so Laravel can use it.





## STEP 6: Create Migration

### Run:

```
php artisan make:migration create_slack_messages_table

```

### Edit: database/migrations/create_slack_messages_table.php

```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('slack_messages', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slack_messages');
    }
};

```


### Then Run:

```
php artisan migrate

```

#### Explanation:

Creates a database table to store Slack messages sent from the application.





## STEP 7: Create Model

### Run:

```
php artisan make:model SlackMessage

```

### app/Models/SlackMessage.php

```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlackMessage extends Model
{
    use HasFactory;

    protected $table = 'slack_messages';

    protected $fillable = [
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

```

#### Explanation:

Creates a model to interact with the slack_messages table using Eloquent ORM.





## STEP 8: Create Controller

### Run:

```
php artisan make:controller SlackController

```

### Edit: app/Http/Controllers/SlackController.php:

```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\SlackAlerts\Facades\SlackAlert;
use App\Models\SlackMessage;

class SlackController extends Controller
{
    public function index()
    {
        return view('slack-form');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255'
        ]);

        // Store in DB
        SlackMessage::create([
            'message' => $request->message
        ]);

        // Send to Slack
        SlackAlert::message($request->message);

        return back()->with('success', 'Message sent & stored!');
    }

    public function list()
    {
        $messages = SlackMessage::latest()->get();
        return view('list', compact('messages'));
    }
}

```

#### Explanation:

Creates a controller to handle form requests, send Slack messages, and store data.





## STEP 9: Add Routes

### Edit routes/web.php:

```
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlackController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/slack-form', [SlackController::class, 'index']);
Route::post('/send-message', [SlackController::class, 'sendMessage']);
Route::get('/messages', [SlackController::class, 'list']);

```

#### Explanation:

Defines URLs for form page, sending messages, and viewing stored messages.




## STEP 10: Create Blade View

### resources/views/slack-form.blade.php

```
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

```



### resources/views/list.blade.php

```
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

```

#### Explanation:

Creates UI pages for sending Slack messages and displaying stored messages.




## STEP 11: Run the App  

### Start dev server:

```
php artisan serve

```

### Open in browser:

```
http://127.0.0.1:8000/slack-form

```

#### Explanation:

Starts the Laravel development server and allows you to access the application in browser.




## Expected Output:

### Home Page:


<img src="screenshots/Screenshot 2026-03-20 115235.png" width="900">


### Slack Message Form:


<img src="screenshots/Screenshot 2026-03-20 115310.png" width="900">


### Success Notification:


<img src="screenshots/Screenshot 2026-03-20 115323.png" width="900">


### Stored Messages List:


<img src="screenshots/Screenshot 2026-03-20 115333.png" width="900">





---

## Project Folder Structure:

```
PHP_Laravel12_Slack_Alerts/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── SlackController.php
│   │
│   └── Models/
│       └── SlackMessage.php
│
├── bootstrap/
│
├── config/
│   └── slack-alerts.php
│
├── database/
│   ├── migrations/
│   │   └── xxxx_xx_xx_create_slack_messages_table.php
│   └── factories/
│   └── seeders/
│
├── public/
│
├── resources/
│   └── views/
│       ├── slack-form.blade.php
│       └── list.blade.php
│
├── routes/
│   └── web.php
│
├── storage/
│
├── tests/
│
├── vendor/
│
├── .env
├── .env.example
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── vite.config.js
├── README.md

```

