<?php

namespace App\Services\Settings;

use App\Models\Parametre;

class SettingsService
{
    /**
     * Get all parameters mapped as a key-value array.
     */
    public function getSettings(): array
    {
        $params = Parametre::all();
        $settings = [];

        foreach ($params as $param) {
            $settings[$param->cle] = json_decode($param->valeur, true);
        }

        return $settings;
    }

    /**
     * Bulk update settings.
     */
    public function updateSettings(array $payload): array
    {
        foreach ($payload as $key => $value) {
            Parametre::updateOrCreate(
                ['cle' => $key],
                ['valeur' => json_encode($value)]
            );
        }

        return $this->getSettings();
    }
}
