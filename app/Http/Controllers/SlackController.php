<?php
// app/Http/Controllers/SlackController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SlackMessage;
use App\Models\AlertTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SlackController extends Controller
{
    // Show form with templates
    public function index()
    {
        $stats = [
            'total' => SlackMessage::count(),
            'today' => SlackMessage::whereDate('created_at', Carbon::today())->count(),
            'urgent' => SlackMessage::where('priority', 'urgent')->count(),
            'failed' => SlackMessage::where('is_sent', false)->whereNotNull('failed_at')->count(),
            'pending' => SlackMessage::where('is_sent', false)->whereNull('failed_at')->count(),
        ];

        $templates = AlertTemplate::where('is_active', true)->get();

        return view('slack-form', compact('stats', 'templates'));
    }

    // Send message with template support
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'sender_name' => 'nullable|string|max:100',
            'sender_email' => 'nullable|email|max:100',
            'priority' => 'required|in:low,normal,high,urgent',
            'category' => 'required|in:info,alert,warning,error',
            'schedule_date' => 'nullable|date',
            'template_id' => 'nullable|exists:alert_templates,id',
            'placeholders' => 'nullable|array'
        ]);

        $messageText = $request->message;
        $templateId = null;

        // If template is used, render with placeholders
        if ($request->template_id && $request->placeholders) {
            $template = AlertTemplate::find($request->template_id);
            if ($template) {
                $messageText = $template->renderContent($request->placeholders);
                $templateId = $template->id;
            }
        }

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
            $messageText;

        if ($request->sender_name) {
            $formattedMessage .= "\n👤 From: " . $request->sender_name;
        }

        if ($request->sender_email) {
            $formattedMessage .= "\n📧 Email: " . $request->sender_email;
        }

        // Save DB
        $message = SlackMessage::create([
            'message' => $messageText,
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'priority' => $request->priority,
            'category' => $request->category,
            'scheduled_at' => $request->schedule_date,
            'is_sent' => $request->schedule_date ? false : true,
            'template_id' => $templateId
        ]);

        // Get webhook URL
        $webhookUrl = config('slack-alerts.webhook_urls.default');

        // Check if webhook URL is configured
        if (empty($webhookUrl)) {
            $message->is_sent = false;
            $message->failed_at = Carbon::now();
            $message->failure_reason = 'Slack webhook URL not configured. Please set SLACK_WEBHOOK_URL in .env file.';
            $message->save();
            
            return back()->with('error', '❌ Slack webhook URL not configured! Please add SLACK_WEBHOOK_URL to your .env file.');
        }

        // Send immediately if no schedule
        if (!$request->schedule_date) {
            try {
                $response = Http::post(
                    $webhookUrl,
                    ['text' => $formattedMessage]
                );

                if ($response->successful()) {
                    $message->is_sent = true;
                    $message->save();
                    $status = 'sent to Slack & ';
                } else {
                    $message->is_sent = false;
                    $message->failed_at = Carbon::now();
                    $message->failure_reason = 'Slack API error: ' . $response->status() . ' - ' . $response->body();
                    $message->save();
                    
                    Log::error('Slack API Error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'message_id' => $message->id
                    ]);
                    
                    $status = 'failed (Slack error) & ';
                }
            } catch (\Exception $e) {
                $message->is_sent = false;
                $message->failed_at = Carbon::now();
                $message->failure_reason = $e->getMessage();
                $message->save();
                
                Log::error('Slack Send Exception', [
                    'error' => $e->getMessage(),
                    'message_id' => $message->id
                ]);
                
                $status = 'failed (exception) & ';
            }
        } else {
            $status = 'scheduled for ' .
                Carbon::parse($request->schedule_date)->format('M d, Y') . ' & ';
        }

        $successMsg = "✅ Message {$status}saved to database!";
        
        if (strpos($status, 'failed') !== false) {
            return back()->with('error', $successMsg);
        }

        return back()->with('success', $successMsg);
    }

    // List messages with failed filter
    public function list(Request $request)
    {
        $search = $request->get('search');
        $priority = $request->get('priority');
        $category = $request->get('category');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $status = $request->get('status');

        $query = SlackMessage::query();

        if ($search) {
            $query->where('message', 'LIKE', "%{$search}%");
        }

        if ($priority && $priority != 'all') {
            $query->where('priority', $priority);
        }

        if ($category && $category != 'all') {
            $query->where('category', $category);
        }

        if ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        }

        if ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        }

        if ($status == 'sent') {
            $query->where('is_sent', true);
        } elseif ($status == 'failed') {
            $query->where('is_sent', false)->whereNotNull('failed_at');
        } elseif ($status == 'pending') {
            $query->where('is_sent', false)->whereNull('failed_at');
        }

        $messages = $query->latest()->paginate(10);

        $stats = [
            'total' => SlackMessage::count(),
            'urgent' => SlackMessage::where('priority', 'urgent')->count(),
            'today' => SlackMessage::whereDate('created_at', Carbon::today())->count(),
            'failed' => SlackMessage::where('is_sent', false)->whereNotNull('failed_at')->count(),
            'pending' => SlackMessage::where('is_sent', false)->whereNull('failed_at')->count(),
        ];

        return view('list', compact(
            'messages',
            'search',
            'priority',
            'category',
            'date_from',
            'date_to',
            'stats',
            'status'
        ));
    }

    // Delete - Now supports both GET and DELETE
    public function delete($id)
    {
        $message = SlackMessage::findOrFail($id);
        $message->delete();
        
        // Check if request expects JSON (for AJAX)
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Deleted successfully']);
        }
        
        return back()->with('success', '🗑️ Message deleted successfully!');
    }

    // Resend (Retry) - with retry count limit
    public function resend($id)
    {
        $message = SlackMessage::findOrFail($id);

        // Check if can retry
        if (!$message->can_retry) {
            return back()->with('error', '❌ This message cannot be retried (max 3 attempts)');
        }

        // Get webhook URL
        $webhookUrl = config('slack-alerts.webhook_urls.default');
        
        if (empty($webhookUrl)) {
            return back()->with('error', '❌ Slack webhook URL not configured! Please set SLACK_WEBHOOK_URL in .env file.');
        }

        $formattedMessage = "📤 Retry Attempt #" . ($message->retry_count + 1) . "\n\n" . $message->message;

        try {
            $response = Http::post(
                $webhookUrl,
                ['text' => $formattedMessage]
            );

            if ($response->successful()) {
                $message->is_sent = true;
                $message->failed_at = null;
                $message->failure_reason = null;
                $message->retry_count = 0;
                $message->save();
                
                return back()->with('success', '✅ Message resent successfully!');
            } else {
                $message->retry_count++;
                $message->failed_at = Carbon::now();
                $message->failure_reason = 'Retry failed: ' . $response->status() . ' - ' . $response->body();
                $message->save();
                
                Log::error('Slack Retry Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'message_id' => $message->id
                ]);
                
                return back()->with('error', '❌ Retry failed. Attempt ' . $message->retry_count . ' of 3');
            }
        } catch (\Exception $e) {
            $message->retry_count++;
            $message->failed_at = Carbon::now();
            $message->failure_reason = $e->getMessage();
            $message->save();
            
            Log::error('Slack Retry Exception', [
                'error' => $e->getMessage(),
                'message_id' => $message->id
            ]);
            
            return back()->with('error', '❌ Retry failed: ' . $e->getMessage());
        }
    }

    // Bulk delete - Fixed with proper validation
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'integer|exists:slack_messages,id'
        ]);
        
        $ids = $request->input('message_ids', []);
        
        if (empty($ids)) {
            return back()->with('error', '❌ No messages selected!');
        }
        
        $count = SlackMessage::whereIn('id', $ids)->delete();
        
        return back()->with('success', '🗑️ ' . $count . ' messages deleted successfully!');
    }

    // Export CSV
    public function export()
    {
        $messages = SlackMessage::all();
        $filename = 'slack_messages_' . date('Y-m-d_H-i-s') . '.csv';

        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['ID', 'Message', 'Sender', 'Email', 'Priority', 'Category', 'Status', 'Failed At', 'Retry Count', 'Created At']);

        foreach ($messages as $message) {
            $status = $message->is_sent ? 'Sent' : ($message->failed_at ? 'Failed' : 'Pending');
            fputcsv($handle, [
                $message->id,
                $message->message,
                $message->sender_name,
                $message->sender_email,
                $message->priority,
                $message->category,
                $status,
                $message->failed_at,
                $message->retry_count,
                $message->created_at
            ]);
        }

        fclose($handle);
        exit;
    }

    // Dashboard
    public function dashboard()
    {
        $totalMessages = SlackMessage::count();
        $failedMessages = SlackMessage::where('is_sent', false)->whereNotNull('failed_at')->count();
        $pendingMessages = SlackMessage::where('is_sent', false)->whereNull('failed_at')->count();

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

        $failedByDay = SlackMessage::where('is_sent', false)
            ->whereNotNull('failed_at')
            ->where('failed_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('DATE(failed_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $templates = AlertTemplate::where('is_active', true)->get();

        return view('dashboard', compact(
            'totalMessages',
            'failedMessages',
            'pendingMessages',
            'messagesByPriority',
            'messagesByCategory',
            'last7Days',
            'failedByDay',
            'templates'
        ));
    }

    // Template Management
    public function templates()
    {
        $templates = AlertTemplate::all();
        return view('templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|in:info,alert,warning,error',
            'priority' => 'required|in:low,normal,high,urgent',
            'content' => 'required|string',
            'placeholders' => 'nullable|json'
        ]);

        AlertTemplate::create([
            'name' => $request->name,
            'category' => $request->category,
            'priority' => $request->priority,
            'content' => $request->content,
            'placeholders' => json_decode($request->placeholders, true) ?: [],
            'is_active' => $request->has('is_active')
        ]);

        return back()->with('success', '✅ Template created successfully!');
    }

    public function deleteTemplate($id)
    {
        AlertTemplate::findOrFail($id)->delete();
        return back()->with('success', '🗑️ Template deleted!');
    }

    public function toggleTemplate($id)
    {
        $template = AlertTemplate::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();
        return back()->with('success', '✅ Template status updated!');
    }
}