<?php

namespace App\Models;

use App\Enums\StatutBilletEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voyage extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Statuts de billet comptant comme une vente réalisée : le billet est payé
     * (ou déjà scanné). Les billets en attente de paiement, expirés ou annulés
     * ne constituent pas une recette.
     */
    public const STATUTS_VENDUS = [
        StatutBilletEnum::PAYE->value,
        StatutBilletEnum::UTILISE->value,
    ];

    protected $fillable = [
        'date_voyage',
        'places',
        'places_restantes',
        'trajet_id',
        'chaloupe_id',
    ];

    protected function casts(): array
    {
        return [
            'date_voyage' => 'date',
        ];
    }

    public function trajet()
    {
        return $this->belongsTo(Trajet::class);
    }

    public function chaloupe()
    {
        return $this->belongsTo(Chaloupe::class);
    }

    public function billets()
    {
        return $this->hasMany(Billet::class);
    }

    /**
     * Ajoute les agrégats de vente du voyage :
     *   - `billets_vendus` : nombre de billets payés ou déjà scannés,
     *   - `recette` : somme des montants correspondants (en FCFA).
     *
     * Les billets gratuits d'abonnés (montant 0) sont comptés dans
     * `billets_vendus` mais ne gonflent pas la recette.
     */
    public function scopeWithVentes(Builder $query): Builder
    {
        return $query
            ->withCount(['billets as billets_vendus' => fn ($q) => $q->whereIn('statut', self::STATUTS_VENDUS)])
            ->withSum(['billets as recette' => fn ($q) => $q->whereIn('statut', self::STATUTS_VENDUS)], 'montant');
    }
}
