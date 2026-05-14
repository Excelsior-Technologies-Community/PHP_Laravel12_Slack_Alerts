<?php
// app/Models/SlackMessage.php

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
        'scheduled_at'
    ];
    
    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_sent' => 'boolean'
    ];
    
    // Scope for priority filtering
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
    
    // Scope for category filtering
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    // Get priority badge color
    public function getPriorityColorAttribute()
    {
        return [
            'low' => 'green',
            'normal' => 'blue',
            'high' => 'orange',
            'urgent' => 'red'
        ][$this->priority] ?? 'gray';
    }
}