<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\City;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    /**
     * Get cities that the current admin can access
     */
    private function getAccessibleCityIds()
    {
        $admin = Auth::guard('admin')->user();
        $adminCities = $admin->cities();
        
        // If admin has no city assignments, they can access all cities (super admin)
        if ($adminCities->count() == 0) {
            return City::pluck('id')->toArray();
        }
        
        // Otherwise, return only assigned cities
        return $adminCities->pluck('cities.id')->toArray();
    }

    /**
     * Apply city restriction to query
     */
    private function applyCityRestriction($query)
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        return $query->whereIn('city_id', $accessibleCityIds);
    }

    // Show all settings
    public function index(Request $request)
    {
        $query = Setting::with('city');
        
        // Apply city restriction based on admin's assigned cities
        $query = $this->applyCityRestriction($query);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('key', 'LIKE', "%{$search}%")
                  ->orWhere('value', 'LIKE', "%{$search}%")
                  ->orWhereHas('city', function($cityQuery) use ($search) {
                      $cityQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // Filter by city (only show cities admin has access to)
        if ($request->filled('city_id')) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (in_array($request->get('city_id'), $accessibleCityIds)) {
                $query->where('city_id', $request->get('city_id'));
            }
        }
        
        // Filter by setting key
        if ($request->filled('key_filter')) {
            $query->where('key', 'LIKE', "%{$request->get('key_filter')}%");
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['key', 'value', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }
        
        $settings = $query->paginate(25)->withQueryString();
        
        // Get filter options - only cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->orderBy('name')->get();
        
        // Get unique setting keys for filter dropdown (from accessible cities only)
        $settingKeys = Setting::whereIn('city_id', $accessibleCityIds)
                             ->distinct()
                             ->pluck('key')
                             ->sort();
        
        return view('setting.index', compact('settings', 'cities', 'settingKeys'));
    }

    // Show the form to create new setting
    public function create()
    {
        $setting = new Setting();
        
        // Only show cities admin has access to
        $accessibleCityIds = $this->getAccessibleCityIds();
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
        return view('setting.edit', compact('setting', 'cities'));
    }

    // Show the form for editing the specified setting
    public function edit(Setting $setting)
    {
        // Check if admin has access to this setting's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($setting->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to edit this setting.');
        }

        // Only show cities admin has access to
        $cities = City::whereIn('id', $accessibleCityIds)->get();
        
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
        // For existing settings, check if admin has access to this setting's city
        if ($setting->exists) {
            $accessibleCityIds = $this->getAccessibleCityIds();
            if (!in_array($setting->city_id, $accessibleCityIds)) {
                abort(403, 'You do not have permission to edit this setting.');
            }
        }

        $isCreating = !$setting->exists;
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        // Validation rules
        $rules = [
            'city_id' => ['required', 'exists:cities,id', function ($attribute, $value, $fail) use ($accessibleCityIds) {
                if (!in_array($value, $accessibleCityIds)) {
                    $fail('You do not have permission to create/edit settings for this city.');
                }
            }],
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ];

        // Check for unique key per city (but allow updating the same setting)
        if ($isCreating || $setting->key !== $request->input('key') || $setting->city_id != $request->input('city_id')) {
            $rules['key'] = [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $accessibleCityIds) {
                    $exists = Setting::where('key', $value)
                                   ->where('city_id', $request->input('city_id'))
                                   ->exists();
                    if ($exists) {
                        $fail('This setting key already exists for the selected city.');
                    }
                }
            ];
        }

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
                ->withErrors('error', 'Something went wrong!');
        }

        $message = $isCreating ? 'Setting has been created successfully' : 'Setting has been updated successfully';

        return redirect()
            ->route('setting.index')
            ->with('status', $message);
    }

    // Show the specified setting details
    public function show(Setting $setting)
    {
        // Check if admin has access to this setting's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($setting->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to view this setting.');
        }

        $setting->load('city');
        return view('setting.show', compact('setting'));
    }

    // Delete the specified setting
    public function destroy(Setting $setting)
    {
        // Check if admin has access to this setting's city
        $accessibleCityIds = $this->getAccessibleCityIds();
        if (!in_array($setting->city_id, $accessibleCityIds)) {
            abort(403, 'You do not have permission to delete this setting.');
        }

        $setting->delete();
        
        return redirect()
            ->route('setting.index')
            ->with('status', 'Setting has been deleted successfully');
    }

    // Bulk actions
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'setting_ids' => 'required|array',
            'setting_ids.*' => 'exists:settings,id'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        $deletedItems = [];
        $errors = [];

        if ($request->action === 'delete') {
            foreach ($request->setting_ids as $settingId) {
                try {
                    $setting = Setting::find($settingId);
                    if (!$setting) continue;

                    // Check if admin has access to this setting's city
                    if (!in_array($setting->city_id, $accessibleCityIds)) {
                        $errors[] = "No permission to delete setting '{$setting->key}' for {$setting->city->name}";
                        continue;
                    }

                    $settingKey = $setting->key;
                    $cityName = $setting->city->name;

                    $setting->delete();
                    $deletedItems[] = "setting '{$settingKey}' from {$cityName}";

                } catch (\Exception $e) {
                    $errors[] = "Error deleting setting '{$settingKey}': " . $e->getMessage();
                }
            }
        }

        $message = "";
        if (!empty($deletedItems)) {
            $message = "Successfully deleted: " . implode(', ', $deletedItems);
        }
        if (!empty($errors)) {
            $message .= (!empty($message) ? " | " : "") . "Errors: " . implode(", ", $errors);
        }

        return redirect()
            ->route('setting.index')
            ->with(!empty($deletedItems) ? 'status' : 'error', $message ?: 'No items were deleted.');
    }

    // Get setting value by key for specific city (API endpoint)
    public function getSettingValue(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'city_id' => 'required|exists:cities,id'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        
        if (!in_array($request->city_id, $accessibleCityIds)) {
            return response()->json([
                'error' => 'You do not have permission to access settings for this city.'
            ], 403);
        }

        $setting = Setting::where('key', $request->key)
                         ->where('city_id', $request->city_id)
                         ->first();

        if (!$setting) {
            return response()->json([
                'error' => 'Setting not found.'
            ], 404);
        }

        return response()->json([
            'key' => $setting->key,
            'value' => $setting->value,
            'city_id' => $setting->city_id,
            'city_name' => $setting->city->name
        ]);
    }

    // Update setting value by key for specific city (API endpoint)
    public function updateSettingValue(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'city_id' => 'required|exists:cities,id',
            'value' => 'required|string'
        ]);

        $accessibleCityIds = $this->getAccessibleCityIds();
        
        if (!in_array($request->city_id, $accessibleCityIds)) {
            return response()->json([
                'error' => 'You do not have permission to update settings for this city.'
            ], 403);
        }

        $setting = Setting::where('key', $request->key)
                         ->where('city_id', $request->city_id)
                         ->first();

        if (!$setting) {
            return response()->json([
                'error' => 'Setting not found.'
            ], 404);
        }

        $setting->value = $request->value;
        $setting->save();

        return response()->json([
            'message' => 'Setting updated successfully.',
            'key' => $setting->key,
            'value' => $setting->value,
            'city_id' => $setting->city_id,
            'city_name' => $setting->city->name
        ]);
    }

    // Get all settings for accessible cities (API endpoint)
    public function getAccessibleSettings()
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        $settings = Setting::with('city')
                          ->whereIn('city_id', $accessibleCityIds)
                          ->get()
                          ->groupBy('city.name');

        return response()->json($settings);
    }

    // Export settings for accessible cities
    public function exportSettings(Request $request)
    {
        $accessibleCityIds = $this->getAccessibleCityIds();
        
        $query = Setting::with('city')->whereIn('city_id', $accessibleCityIds);
        
        // Filter by city if provided
        if ($request->filled('city_id') && in_array($request->city_id, $accessibleCityIds)) {
            $query->where('city_id', $request->city_id);
        }
        
        $settings = $query->orderBy('city_id')->orderBy('key')->get();
        
        $filename = 'settings_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($settings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['City', 'Key', 'Value', 'Created At', 'Updated At']);
            
            foreach ($settings as $setting) {
                fputcsv($file, [
                    $setting->city->name,
                    $setting->key,
                    $setting->value,
                    $setting->created_at,
                    $setting->updated_at
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}