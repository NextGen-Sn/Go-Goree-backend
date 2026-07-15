<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Services\Settings\SettingsService;
use Illuminate\Http\Request;

class ParametreController extends Controller
{
    public function __construct(protected SettingsService $settingsService) {}

    /**
     * Get system settings.
     */
    public function index()
    {
        return response()->json($this->settingsService->getSettings());
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $settings = $this->settingsService->updateSettings($request->all());
        return response()->json($settings);
    }
}
