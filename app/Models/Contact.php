<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'email', 'subject', 'body', 'status', 'admin_note'])]
class Contact extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'new'         => '新規',
            'in_progress' => '対応中',
            'resolved'    => '解決済み',
            default       => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'new'         => 'bg-red-100 text-red-700',
            'in_progress' => 'bg-yellow-100 text-yellow-700',
            'resolved'    => 'bg-green-100 text-green-700',
            default       => 'bg-gray-100 text-gray-600',
        };
    }
}
