<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'schedule_date' => 'nullable|date'
        ]);

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

        $formattedMessage =
            $priorityEmoji[$request->priority] . " " .
            $categoryEmoji[$request->category] . " " .
            $request->message;

        if ($request->sender_name) {
            $formattedMessage .= "\n👤 From: " . $request->sender_name;
        }

        if ($request->sender_email) {
            $formattedMessage .= "\n📧 Email: " . $request->sender_email;
        }

        // Save DB
        $message = SlackMessage::create([
            'message' => $request->message,
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'priority' => $request->priority,
            'category' => $request->category,
            'scheduled_at' => $request->schedule_date,
            'is_sent' => $request->schedule_date ? false : true
        ]);

        // Send immediately if no schedule
        if (!$request->schedule_date) {

            $response = Http::post(
                config('slack-alerts.webhook_urls.default'),
                [
                    'text' => $formattedMessage
                ]
            );

            if ($response->successful()) {
                $status = 'sent to Slack & ';
            } else {
                $status = 'saved only (Slack failed) & ';
            }

        } else {

            $status = 'scheduled for ' .
                Carbon::parse($request->schedule_date)
                ->format('M d, Y') . ' & ';
        }

        return back()->with(
            'success',
            "✅ Message {$status}saved to database!"
        );
    }

    // List messages
    public function list(Request $request)
    {
        $search = $request->get('search');
        $priority = $request->get('priority');
        $category = $request->get('category');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');

        $query = SlackMessage::query();

        if ($search) {
            $query->where(
                'message',
                'LIKE',
                "%{$search}%"
            );
        }

        if ($priority && $priority != 'all') {
            $query->where('priority', $priority);
        }

        if ($category && $category != 'all') {
            $query->where('category', $category);
        }

        if ($date_from) {
            $query->whereDate(
                'created_at',
                '>=',
                $date_from
            );
        }

        if ($date_to) {
            $query->whereDate(
                'created_at',
                '<=',
                $date_to
            );
        }

        $messages = $query->latest()->paginate(10);

        $stats = [
            'total' => SlackMessage::count(),
            'urgent' => SlackMessage::where('priority', 'urgent')->count(),
            'today' => SlackMessage::whereDate('created_at', Carbon::today())->count(),
            'this_week' => SlackMessage::whereBetween(
                'created_at',
                [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]
            )->count()
        ];

        return view(
            'list',
            compact(
                'messages',
                'search',
                'priority',
                'category',
                'date_from',
                'date_to',
                'stats'
            )
        );
    }

    // Delete
    public function delete($id)
    {
        SlackMessage::findOrFail($id)->delete();

        return back()->with(
            'success',
            '🗑️ Message deleted successfully!'
        );
    }

    // Resend
    public function resend($id)
    {
        $message = SlackMessage::findOrFail($id);

        $formattedMessage =
            "📤 Resent Message\n\n" .
            $message->message;

        Http::post(
            config('slack-alerts.webhook_urls.default'),
            [
                'text' => $formattedMessage
            ]
        );

        return back()->with(
            'success',
            '📤 Message resent successfully!'
        );
    }

    // Bulk delete
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array'
        ]);

        SlackMessage::whereIn(
            'id',
            $request->message_ids
        )->delete();

        return back()->with(
            'success',
            '🗑️ Messages deleted successfully!'
        );
    }

    // Export CSV
    public function export()
    {
        $messages = SlackMessage::all();

        $filename =
            'slack_messages_' .
            date('Y-m-d_H-i-s') .
            '.csv';

        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header(
            'Content-Disposition: attachment; filename="' .
            $filename . '"'
        );

        fputcsv($handle, [
            'ID',
            'Message',
            'Sender',
            'Email',
            'Priority',
            'Category',
            'Created At'
        ]);

        foreach ($messages as $message) {

            fputcsv($handle, [
                $message->id,
                $message->message,
                $message->sender_name,
                $message->sender_email,
                $message->priority,
                $message->category,
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

        $messagesByPriority =
            SlackMessage::selectRaw(
                'priority, count(*) as count'
            )
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $messagesByCategory =
            SlackMessage::selectRaw(
                'category, count(*) as count'
            )
            ->groupBy('category')
            ->pluck('count', 'category');

        $last7Days =
            SlackMessage::where(
                'created_at',
                '>=',
                Carbon::now()->subDays(7)
            )
            ->selectRaw(
                'DATE(created_at) as date,
                 count(*) as count'
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view(
            'dashboard',
            compact(
                'totalMessages',
                'messagesByPriority',
                'messagesByCategory',
                'last7Days'
            )
        );
    }
}