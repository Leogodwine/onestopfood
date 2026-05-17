<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminConfigController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get();

        return view('admin.config', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => ['array'],
        ]);

        foreach ($data['settings'] ?? [] as $key => $value) {
            $setting = SystemSetting::firstOrNew(['key' => $key]);
            $setting->value = $value;
            if (! $setting->label) {
                $setting->label = ucwords(str_replace('_', ' ', $key));
            }
            $setting->save();
        }

        Cache::forget('settings.site_name');
        Cache::forget('settings.currency');
        Cache::forget('settings.support_phone');

        return back()->with('status', 'Configuration updated.');
    }
}

