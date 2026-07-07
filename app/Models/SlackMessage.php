<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlackMessage extends Model
{
    protected $fillable = [
        'message', 
        'sender_name', 
        'sender_email', 
        'priority', 
        'category',
        'is_sent',
        'scheduled_at',
        'failed_at',
        'retry_count',
        'failure_reason',
        'template_id'
    ];
    
    protected $casts = [
        'scheduled_at' => 'datetime',
        'failed_at' => 'datetime',
        'is_sent' => 'boolean'
    ];
    
    // Scopes
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
    
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    public function scopeFailed($query)
    {
        return $query->where('is_sent', false)->whereNotNull('failed_at');
    }
    
    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }
    
    public function scopePending($query)
    {
        return $query->where('is_sent', false)->whereNull('failed_at');
    }
    
    // Accessors
    public function getPriorityColorAttribute()
    {
        return [
            'low' => 'green',
            'normal' => 'blue',
            'high' => 'orange',
            'urgent' => 'red'
        ][$this->priority] ?? 'gray';
    }
    
    public function getStatusBadgeAttribute()
    {
        if ($this->is_sent) {
            return '<span class="badge badge-success">✅ Sent</span>';
        }
        
        if ($this->failed_at) {
            return '<span class="badge badge-danger">❌ Failed</span>';
        }
        
        return '<span class="badge badge-warning">⏳ Pending</span>';
    }
    
    public function getCanRetryAttribute()
    {
        return !$this->is_sent && $this->failed_at && $this->retry_count < 3;
    }
    
    // Relationships
    public function template()
    {
        return $this->belongsTo(AlertTemplate::class, 'template_id');
    }
}