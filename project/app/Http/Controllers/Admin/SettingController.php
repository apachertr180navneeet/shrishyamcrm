<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocietySetting;
use App\Services\AuditService;

class SettingController extends Controller
{
    /**
     * Whitelist of settings keys that may be updated from the settings form.
     * Prevents arbitrary configuration injection via crafted form fields.
     */
    private const ALLOWED_KEYS = [
        'society_name',
        'society_name_hindi',
        'reg_no',
        'san_prefix',
        'address',
        'phone',
        'email',
        'president_name',
        'secretary_name',
        'treasurer_name',
        'default_event_rate',
        'default_commission',
    ];

    public function index()
    {
        $settings = SocietySetting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->only(self::ALLOWED_KEYS);

        foreach ($data as $key => $val) {
            if ($val !== null) {
                SocietySetting::setVal($key, $val);
            }
        }

        AuditService::log('update', 'settings', null, null, $data);

        return back()->with('success', 'Society settings updated successfully.');
    }
}
