<?php

namespace App\Http\Controllers\Migrations;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\NotificationCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\OldNotification;
use App\Models\Notification;
use App\Models\Media;
use App\Models\Service;
use App\Models\News;

class NotificationMigrationController extends Controller
{
    /**
     * Migrate notifications in batches to avoid timeout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateNotifications(Request $request)
    {
        // Set a higher execution time limit
        ini_set('max_execution_time', 600); // 10 minutes
        
        try {
            // Get the batch size and starting ID from request or use defaults
            $batchSize = $request->input('batch_size', 50);
            $startId = $request->input('start_id', 0);
            
            // Get a batch of notifications
            $oldNotifications = OldNotification::with(['institute', 'news', 'cities'])
                ->where('id', '>', $startId)
                ->orderBy('id')
                ->limit($batchSize)
                ->get();
            
            $totalCount = OldNotification::count();
            $remainingCount = OldNotification::where('id', '>', $startId)->count();
            
            if ($oldNotifications->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'All notifications have been migrated',
                    'total_count' => $totalCount,
                    'migrated_count' => $totalCount,
                    'remaining_count' => 0,
                    'progress_percentage' => 100
                ]);
            }
            
            $migrationResults = [
                'total_notifications' => count($oldNotifications),
                'migrated_notifications' => 0,
                'failed_notifications' => 0,
                'notification_details' => [],
                'last_id' => $startId
            ];
            
            foreach ($oldNotifications as $oldNotification) {
                // Start a database transaction for this single notification
                DB::beginTransaction();
                
                try {
                    $result = $this->migrateNotification($oldNotification);
                    
                    if ($result['success']) {
                        $migrationResults['migrated_notifications']++;
                    } else {
                        $migrationResults['failed_notifications']++;
                    }
                    
                    $migrationResults['notification_details'][] = $result;
                    $migrationResults['last_id'] = $oldNotification->id;
                    
                    // Commit the transaction
                    DB::commit();
                } catch (\Exception $e) {
                    // Rollback transaction for this notification only
                    DB::rollBack();
                    
                    Log::error('Notification migration failed: ' . $e->getMessage(), [
                        'notification_id' => $oldNotification->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    $migrationResults['failed_notifications']++;
                    $migrationResults['notification_details'][] = [
                        'success' => false,
                        'notification_id' => $oldNotification->id,
                        'message' => 'Migration failed: ' . $e->getMessage()
                    ];
                    $migrationResults['last_id'] = $oldNotification->id;
                }
            }
            
            // Calculate progress
            $nextBatchExists = OldNotification::where('id', '>', $migrationResults['last_id'])->exists();
            $processed = $totalCount - $remainingCount + $migrationResults['migrated_notifications'];
            $progressPercentage = round(($processed / $totalCount) * 100, 2);
            
            return response()->json([
                'success' => true,
                'message' => 'Notifications batch migrated successfully',
                'results' => $migrationResults,
                'progress' => [
                    'total_count' => $totalCount,
                    'processed_count' => $processed,
                    'remaining_count' => $remainingCount - count($oldNotifications),
                    'progress_percentage' => $progressPercentage,
                    'is_complete' => !$nextBatchExists
                ],
                'next_batch' => $nextBatchExists ? [
                    'start_id' => $migrationResults['last_id'],
                    'batch_size' => $batchSize
                ] : null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Notification batch migration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Notification migration failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate a single notification by ID
     *
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateSingleNotification($notificationId)
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            $oldNotification = OldNotification::with(['institute', 'news', 'cities'])->findOrFail($notificationId);
            
            $result = $this->migrateNotification($oldNotification);
            
            // Commit the transaction if everything went well
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $result['success'] ? 'Notification migrated successfully' : 'Notification migration failed',
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            
            Log::error('Notification migration failed: ' . $e->getMessage(), [
                'notification_id' => $notificationId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Notification migration failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check migration status for notifications
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotificationMigrationStatus()
    {
        $oldNotificationsCount = OldNotification::count();
        $newNotificationsCount = Notification::count();
        
        return response()->json([
            'success' => true,
            'status' => [
                'old_notifications_count' => $oldNotificationsCount,
                'new_notifications_count' => $newNotificationsCount,
                'progress_percentage' => $oldNotificationsCount > 0 ? 
                    round(($newNotificationsCount / $oldNotificationsCount) * 100, 2) : 0,
                'remaining_notifications' => $oldNotificationsCount - $newNotificationsCount > 0 ? 
                    $oldNotificationsCount - $newNotificationsCount : 0
            ]
        ]);
    }
    
    /**
     * Migrate a specific notification
     *
     * @param OldNotification $oldNotification
     * @return array
     */
    private function migrateNotification(OldNotification $oldNotification)
    {
        try {
            $existingNotification = Notification::where('id', $oldNotification->id)->first();
                
            if ($existingNotification) {
                return [
                    'success' => false,
                    'notification_id' => $oldNotification->id,
                    'message' => "Notification '{$oldNotification->title}' already exists"
                ];
            }
            
            // Find related service (if applicable)
            $serviceId = null;
            if ($oldNotification->institute_id) {
                $service = DB::table('services')->where('id', $oldNotification->institute_id)
                    ->first();
                
                if ($service) {
                    $serviceId = $service->id;
                }
            }
            
            // Find related news (if applicable)
            $newsId = null;
            if ($oldNotification->news_id) {
                $news = DB::table('news')->where('id', $oldNotification->news_id)->first();
                
                if ($news) {
                    $newsId = $news->id;
                }
            }
            
            // Handle notification image if exists
            $imageId = null;
            if (!empty($oldNotification->image)) {
                $media = $this->createMedia($oldNotification->image);
                $imageId = $media->id;
            }
            
            // Create the new notification
            $newNotification = new Notification();
            $newNotification->id = $oldNotification->id;
            $newNotification->title = $oldNotification->title;
            $newNotification->body = $oldNotification->body;
            $newNotification->service_id = $serviceId;
            $newNotification->news_id = $newsId;
            $newNotification->image_id = $imageId;
            $newNotification->created_at = $oldNotification->created_at;
            $newNotification->updated_at = $oldNotification->updated_at;
            $newNotification->save();
            
            $this->migrateNotificationCities($newNotification, $oldNotification);

            return [
                'success' => true,
                'notification_id' => [
                    'old' => $oldNotification->id,
                    'new' => $newNotification->id
                ],
                'title' => $newNotification->title,
                'related_to' => [
                    'service_id' => $serviceId,
                    'news_id' => $newsId
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Notification migration failed: ' . $e->getMessage(), [
                'notification_id' => $oldNotification->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'notification_id' => $oldNotification->id,
                'message' => 'Migration failed: ' . $e->getMessage()
            ];
        }
    }


    private function migrateNotificationCities(Notification $notification, OldNotification $oldNotification)
    {
        if ($oldNotification->cities) {
            foreach ($oldNotification->cities as $city)
            {
                if (City::find($city->city_id)) {
                    NotificationCity::create([
                        'notification_id' => $notification->id,
                        'city_id' => $city->city_id
                    ]);
                }
            }
        }
    }
}
