<?php
// app/Http/Controllers/SlackController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\SlackAlerts\Facades\SlackAlert;
use App\Models\SlackMessage;
use Carbon\Carbon;

class SlackController extends Controller
{
    // Show form
    public function index()
    {
        $stats = [
            'total' => SlackMessage::count(),
            'today' => SlackMessage::whereDate('created_at', Carbon::today())->count(),
            'urgent' => SlackMessage::where('priority', 'urgent')->count(),
        ];
        return view('slack-form', compact('stats'));
    }
    
    // Send message
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'sender_name' => 'nullable|string|max:100',
            'sender_email' => 'nullable|email|max:100',
            'priority' => 'required|in:low,normal,high,urgent',
            'category' => 'required|in:info,alert,warning,error',
            'schedule_date' => 'nullable|date|after:now'
        ]);
        
        // Prepare message with priority
        $priorityEmoji = [
            'low' => '✅',
            'normal' => '📝',
            'high' => '⚠️',
            'urgent' => '🔴'
        ];
        
        $categoryEmoji = [
            'info' => 'ℹ️',
            'alert' => '🚨',
            'warning' => '⚠️',
            'error' => '❌'
        ];
        
        $formattedMessage = $priorityEmoji[$request->priority] . " " . 
                           $categoryEmoji[$request->category] . " " .
                           $request->message;
        
        if ($request->sender_name) {
            $formattedMessage .= "\n👤 From: " . $request->sender_name;
        }
        
        // Store in DB
        $message = SlackMessage::create([
            'message' => $request->message,
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'priority' => $request->priority,
            'category' => $request->category,
            'scheduled_at' => $request->schedule_date,
            'is_sent' => $request->schedule_date ? false : true
        ]);
        
        // Send to Slack (if not scheduled)
        if (!$request->schedule_date) {
            SlackAlert::message($formattedMessage);
            $status = 'sent & ';
        } else {
            $status = 'scheduled for ' . Carbon::parse($request->schedule_date)->format('M d, Y h:i A') . ' & ';
        }
        
        return back()->with('success', "✅ Message {$status}saved to database!");
    }
    
    // List messages with filters
    public function list(Request $request)
    {
        $search = $request->get('search');
        $priority = $request->get('priority');
        $category = $request->get('category');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        
        $query = SlackMessage::query();
        
        // Search in message
        if ($search) {
            $query->where('message', 'LIKE', "%{$search}%");
        }
        
        // Filter by priority
        if ($priority && $priority != 'all') {
            $query->where('priority', $priority);
        }
        
        // Filter by category
        if ($category && $category != 'all') {
            $query->where('category', $category);
        }
        
        // Date range filter
        if ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        }
        if ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        }
        
        $messages = $query->latest()->paginate(10);
        
        // Get statistics
        $stats = [
            'total' => SlackMessage::count(),
            'urgent' => SlackMessage::where('priority', 'urgent')->count(),
            'today' => SlackMessage::whereDate('created_at', Carbon::today())->count(),
            'this_week' => SlackMessage::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count()
        ];
        
        return view('list', compact('messages', 'search', 'priority', 'category', 'date_from', 'date_to', 'stats'));
    }
    
    // Delete message
    public function delete($id)
    {
        $message = SlackMessage::findOrFail($id);
        $message->delete();
        
        return back()->with('success', '🗑️ Message deleted successfully!');
    }
    
    // Resend message to Slack
    public function resend($id)
    {
        $message = SlackMessage::findOrFail($id);
        
        $priorityEmoji = [
            'low' => '✅',
            'normal' => '📝',
            'high' => '⚠️',
            'urgent' => '🔴'
        ];
        
        $categoryEmoji = [
            'info' => 'ℹ️',
            'alert' => '🚨',
            'warning' => '⚠️',
            'error' => '❌'
        ];
        
        $formattedMessage = $priorityEmoji[$message->priority] . " " . 
                           $categoryEmoji[$message->category] . " " .
                           $message->message;
        
        if ($message->sender_name) {
            $formattedMessage .= "\n👤 From: " . $message->sender_name;
        }
        
        SlackAlert::message($formattedMessage);
        
        return back()->with('success', '📤 Message resent to Slack successfully!');
    }
    
    // Bulk delete
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array'
        ]);
        
        SlackMessage::whereIn('id', $request->message_ids)->delete();
        
        return back()->with('success', '🗑️ ' . count($request->message_ids) . ' messages deleted successfully!');
    }
    
    // Export messages
    public function export(Request $request)
    {
        $messages = SlackMessage::all();
        
        $filename = 'slack_messages_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Add headers
        fputcsv($handle, ['ID', 'Message', 'Sender', 'Email', 'Priority', 'Category', 'Created At']);
        
        // Add data
        foreach ($messages as $message) {
            fputcsv($handle, [
                $message->id,
                $message->message,
                $message->sender_name ?? 'N/A',
                $message->sender_email ?? 'N/A',
                $message->priority,
                $message->category,
                $message->created_at
            ]);
        }
        
        fclose($handle);
        exit;
    }
    
    // Dashboard statistics
    public function dashboard()
    {
        $totalMessages = SlackMessage::count();
        $messagesByPriority = SlackMessage::selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');
        
        $messagesByCategory = SlackMessage::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');
        
        $last7Days = SlackMessage::where('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('dashboard', compact('totalMessages', 'messagesByPriority', 'messagesByCategory', 'last7Days'));
    }
}