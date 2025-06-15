<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\ContactUsRequest;
use App\Http\Resources\API\ContactUsResource;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContactUsController extends InitController
{
    public function __construct()
    {
        parent::__construct();
        $this->pipeline->setModel('ContactUs');
    }

    /**
     * Send contact us message
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(ContactUsRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create contact us record
            $contactUs = $this->pipeline->create([
                'name' => $request->name,
                'phone' => $request->phone,
                'message' => $request->message,
                'city_id' => $this->user->city_id,
                'user_id' => $this->user->id,
                'is_read' => false,
            ]);

            DB::commit();

            $data = new ContactUsResource($contactUs);

            return jsonResponse(201, 'Message sent successfully. We will contact you soon.', $data);

        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(500, 'Failed to send message. Please try again.');
        }
    }

    /**
     * Get user's contact messages history
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getMyMessages(Request $request): JsonResponse
    {
        try {
            $limit = $request->query('limit', 10);
            $page = $request->query('page', 1);
            $offset = ($page - 1) * $limit;

            $messages = $this->pipeline
                ->where('user_id', $this->user->id)
                ->where('city_id', $this->user->city_id)
                ->with(['city'])
                ->orderBy('created_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $data = ContactUsResource::collection($messages);

            return jsonResponse(200, 'Messages retrieved successfully.', $data);

        } catch (\Exception $e) {
            return jsonResponse(500, 'Failed to retrieve messages. Please try again.');
        }
    }

    /**
     * Get specific contact message by ID (only user's own messages)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getMessageById(Request $request, int $id): JsonResponse
    {
        try {
            $message = $this->pipeline
                ->where('user_id', $this->user->id)
                ->where('city_id', $this->user->city_id)
                ->with(['city', 'user'])
                ->find($id);

            if (!$message) {
                return jsonResponse(404, 'Message not found.');
            }

            $data = new ContactUsResource($message);

            return jsonResponse(200, 'Message retrieved successfully.', $data);

        } catch (\Exception $e) {
            return jsonResponse(500, 'Failed to retrieve message. Please try again.');
        }
    }

    /**
     * Send anonymous contact message (for non-authenticated users)
     * Note: This would need to be in a separate route without auth middleware
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sendAnonymousMessage(ContactUsRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create anonymous contact us record
            $contactUs = $this->pipeline->create([
                'name' => $request->name,
                'phone' => $request->phone,
                'message' => $request->message,
                'city_id' => $this->user->city_id,
                'user_id' => null, // Anonymous
                'is_read' => false,
            ]);

            DB::commit();

            // Load the created record with relationships
            $contactUs = ContactUs::with(['city'])->find($contactUs->id);

            $data = new ContactUsResource($contactUs);

            return jsonResponse(201, 'Message sent successfully. We will contact you soon.', $data);

        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(500, 'Failed to send message. Please try again.');
        }
    }

    /**
     * Update contact message (only for unread messages)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateMessage(ContactUsRequest $request, int $id): JsonResponse
    {
        try {
            $contactUs = ContactUs::where('user_id', $this->user->id)
                ->where('city_id', $this->user->city_id)
                ->where('is_read', false) // Only allow editing unread messages
                ->find($id);

            if (!$contactUs) {
                return jsonResponse(404, 'Message not found or already processed.');
            }

            DB::beginTransaction();

            // Update only provided fields
            if ($request->has('name')) {
                $contactUs->name = $request->name;
            }
            
            if ($request->has('phone')) {
                $contactUs->phone = $request->phone;
            }
            
            if ($request->has('message')) {
                $contactUs->message = $request->message;
            }

            $contactUs->save();

            DB::commit();

            // Load updated record with relationships
            $contactUs = ContactUs::with(['city', 'user'])->find($contactUs->id);

            $data = new ContactUsResource($contactUs);

            return jsonResponse(200, 'Message updated successfully.', $data);

        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(500, 'Failed to update message. Please try again.');
        }
    }

    /**
     * Delete contact message (only for unread messages)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function deleteMessage(Request $request, int $id): JsonResponse
    {
        try {
            $contactUs = ContactUs::where('user_id', $this->user->id)
                ->where('city_id', $this->user->city_id)
                ->where('is_read', false) // Only allow deleting unread messages
                ->find($id);

            if (!$contactUs) {
                return jsonResponse(404, 'Message not found or already processed.');
            }

            DB::beginTransaction();

            $contactUs->delete();

            DB::commit();

            return jsonResponse(200, 'Message deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(500, 'Failed to delete message. Please try again.');
        }
    }
}