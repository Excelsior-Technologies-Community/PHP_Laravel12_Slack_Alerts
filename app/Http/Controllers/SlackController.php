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