<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertTemplate extends Model
{
    protected $fillable = [
        'name',
        'category',
        'priority',
        'content',
        'placeholders',
        'is_active'
    ];
    
    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean'
    ];
    
    public function messages()
    {
        return $this->hasMany(SlackMessage::class);
    }
    
    public function renderContent(array $values = [])
    {
        $content = $this->content;
        
        foreach ($values as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        
        return $content;
    }
    
    public function getPlaceholderListAttribute()
    {
        if (!$this->placeholders) {
            return [];
        }
        
        return array_keys($this->placeholders);
    }
}