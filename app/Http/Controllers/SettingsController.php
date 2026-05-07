<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = CompanySetting::first() ?? new CompanySetting();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name'             => 'required|string|max:255',
            'company_email'            => 'nullable|email',
            'company_phone'            => 'nullable|string|max:30',
            'company_address'          => 'nullable|string',
            'currency'                 => 'required|string|max:10',
            'currency_symbol'          => 'required|string|max:5',
            'working_days_per_month'   => 'required|integer|min:1|max:31',
            'logo'                     => 'nullable|image|max:2048',
        ]);

        $settings = CompanySetting::firstOrNew([]);

        if ($request->hasFile('logo')) {
            if ($settings->logo) Storage::disk('public')->delete($settings->logo);
            $validated['logo'] = $request->file('logo')->store('company', 'public');
        }

        $settings->fill($validated)->save();
        ActivityLog::record('settings_updated', 'Company settings updated');

        return back()->with('success', 'Settings saved.');
    }

    public function activityLogs(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->orderBy('created_at', 'desc')
            ->paginate(50)->withQueryString();

        return view('settings.activity-logs', compact('logs'));
    }
}
