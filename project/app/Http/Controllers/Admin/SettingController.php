<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocietySetting;
use App\Services\AuditService;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SocietySetting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $val) {
            SocietySetting::setVal($key, $val);
        }

        AuditService::log('update', 'settings', null, null, $data);

        return back()->with('success', 'Society settings updated successfully.');
    }
}
