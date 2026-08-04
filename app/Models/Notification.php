<?php

namespace App\Models;

use App\Enums\CanalEnum;
use App\Enums\NotificationEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'type',
        'titre',
        'message',
        'canal',
        'lu_a',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationEnum::class,
            'canal' => CanalEnum::class,
            'lu_a' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
