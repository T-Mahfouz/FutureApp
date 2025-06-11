<?php

namespace App\Http\Controllers\Migrations;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\OldAbout;
use App\Models\Setting;
use Illuminate\Http\Request;
use Log;

class SettingsMigrationController extends Controller
{
    public function migrateAboutUs()
    {
        $counter = 0;
        $errors = [];
        $items = OldAbout::get();

        try {
            foreach ($items as $aboutUs) {
                try {
                    
                    $city = City::where('id', $aboutUs->city_id)->first();
                    if ($city) {
                        Setting::create([
                            'city_id' => $city->id,	
                            'key' => 'about us',	
                            'value' => $aboutUs->content 
                        ]);
                        $counter++;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to migrate contact us item', [
                        'contact_id' => $oldContact->id ?? 'unknown',
                        'contact_name' => $oldContact->name ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to migrate contact us data', [
                'error' => $e->getMessage()
            ]);
            $errors[] = $e->getMessage();
            throw $e;
        }
        
        return response()->json([
            'success' => true,
            'total_processed' => $counter,
            'errors_counter' => count($errors),
            'errors' => $errors
        ]);
    }
}
