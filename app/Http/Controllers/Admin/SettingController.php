<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\City;

class SettingController extends Controller
{
    // Predefined setting types
    private $settingTypes = [
        'about_us' => 'About Us',
        'terms_conditions' => 'Terms & Conditions',
        'privacy_policy' => 'Privacy Policy',
        'contact_info' => 'Contact Information',
        'help_support' => 'Help & Support',
        'app_config' => 'App Configuration',
        'social_media' => 'Social Media Links',
        'notification_settings' => 'Notification Settings',
        'custom' => 'Custom Setting'
    ];

    // Show all settings
    public function index(Request $request)
    {
        $query = Setting::with('city');
        
        // Filter by city if specified
        if($request->has('city_id') && $request->city_id != ''){
            $query->where('city_id', $request->city_id);
        }
        
        // Filter by setting type if specified
        if($request->has('type') && $request->type != ''){
            $query->where('key', 'like', $request->type . '%');
        }
        
        // Search functionality
        if($request->has('search') && $request->search != ''){
            $query->where(function($q) use ($request){
                $q->where('key', 'like', '%' . $request->search . '%')
                  ->orWhere('value', 'like', '%' . $request->search . '%');
            });
        }

        $settings = $query->orderBy('city_id')->orderBy('key')->paginate(25);
        $cities = City::orderBy('name')->get();
        $settingTypes = $this->settingTypes;

        return view('setting.index', compact('settings', 'cities', 'settingTypes'));
    }

    // Show the form to create new setting
    public function create()
    {
        $setting = new Setting();
        $cities = City::orderBy('name')->get();
        $settingTypes = $this->settingTypes;
        return view('setting.edit', compact('setting', 'cities', 'settingTypes'));
    }

    // Show the form for editing the specified setting
    public function edit(Setting $setting)
    {
        $cities = City::orderBy('name')->get();
        $settingTypes = $this->settingTypes;
        return view('setting.edit', compact('setting', 'cities', 'settingTypes'));
    }

    // Save a newly created setting
    public function store(Request $request)
    {
        $setting = new Setting();
        return $this->update($request, $setting);
    }

    // Update the specified setting
    public function update(Request $request, Setting $setting)
    {
        // Validation rules
        $rules = [
            'city_id' => 'required|exists:cities,id',
            'key' => 'required|string|max:255',
            'value' => 'required|string',
            'setting_type' => 'required|string'
        ];

        // Check for unique key per city (except current setting)
        if($setting->id){
            $rules['key'] .= '|unique:settings,key,' . $setting->id . ',id,city_id,' . $request->city_id;
        } else {
            $rules['key'] .= '|unique:settings,key,NULL,id,city_id,' . $request->city_id;
        }

        $request->validate($rules);

        // Prepare the key based on setting type
        $key = $request->input('key');
        if($request->setting_type !== 'custom'){
            $key = $request->setting_type;
        }

        // Update setting fields
        $setting->city_id = $request->input('city_id');
        $setting->key = $key;
        $setting->value = $request->input('value');

        $setting->save();

        $message = $setting->wasRecentlyCreated ? 'Setting has been created successfully' : 'Setting has been updated successfully';

        return redirect()
            ->route('setting.index')
            ->with('status', $message);
    }

    // Show the specified setting
    public function show(Setting $setting)
    {
        $setting->load('city');
        return view('setting.show', compact('setting'));
    }

    // Delete the specified setting
    public function destroy(Setting $setting)
    {
        $setting->delete();
        
        return redirect()
            ->route('setting.index')
            ->with('status', 'Setting has been deleted successfully');
    }

    // Bulk update settings for a city
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required|string',
        ]);

        $cityId = $request->input('city_id');
        $settings = $request->input('settings');

        foreach($settings as $settingData){
            Setting::updateOrCreate(
                [
                    'city_id' => $cityId,
                    'key' => $settingData['key']
                ],
                [
                    'value' => $settingData['value']
                ]
            );
        }

        return redirect()
            ->route('setting.index', ['city_id' => $cityId])
            ->with('status', 'Settings have been updated successfully');
    }

    // Show bulk edit form for a city
    public function bulkEdit(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id'
        ]);

        $cityId = $request->input('city_id');
        $city = City::findOrFail($cityId);
        $settings = Setting::where('city_id', $cityId)->get();
        $settingTypes = $this->settingTypes;

        return view('setting.bulk-edit', compact('city', 'settings', 'settingTypes'));
    }
}