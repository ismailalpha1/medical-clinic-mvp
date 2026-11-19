<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'visit_reason', 'calendar_event_id', 'consent'];

    protected function casts(): array
    {
        return ['consent' => 'boolean'];
    }
}
