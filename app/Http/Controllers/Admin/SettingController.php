<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\City;

class SettingController extends Controller
{
    // Show all settings
    public function index()
    {
        $settings = Setting::with('city')
                          ->paginate(25);
        return view('setting.index', compact('settings'));
    }

    // Show the form to create new setting
    public function create()
    {
        $setting = new Setting();
        $cities = City::all();
        return view('setting.edit', compact('setting', 'cities'));
    }

    // Show the form for editing the specified setting
    public function edit(Setting $setting)
    {
        $cities = City::all();
        return view('setting.edit', compact('setting', 'cities'));
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
        $isCreating = !$setting->exists;
        
        // Validation rules
        $rules = [
            'city_id' => 'required|exists:cities,id',
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ];
        

        $request->validate($rules);

        // Update setting fields
        $setting->fill([
            'city_id' => $request->input('city_id'),
            'key' => $request->input('key'),
            'value' => $request->input('value'),
        ]);

        if(!$setting->save()) {
            return redirect()
                ->route('setting.index')
                ->withErrors('error', 'something went wrong!');
        }

        $message = $isCreating ? 'Setting has been created successfully' : 'Setting has been updated successfully';

        return redirect()
            ->route('setting.index')
            ->with('status', $message);
    }

    // Show the specified setting details
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
}