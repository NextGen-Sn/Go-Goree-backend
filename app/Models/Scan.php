<?php

namespace App\Models;

use App\Enums\ResultatScanEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'resultat',
        'billet_id',
        'embarquement_id',
        'scanne_par',
    ];

    protected function casts(): array
    {
        return [
            'resultat' => ResultatScanEnum::class,
        ];
    }

    public function billet()
    {
        return $this->belongsTo(Billet::class);
    }

    public function embarquement()
    {
        return $this->belongsTo(Embarquement::class);
    }
}
