<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::with('image')->get();
        return response()->json(['data' => $admins], 200);
    }

    public function store(Request $request)
    {
        $validator = validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
            'image_id' => 'nullable|exists:media,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'image_id' => $request->image_id,
        ]);

        return response()->json(['data' => $admin, 'message' => 'Admin created successfully'], 201);
    }

    public function show($id)
    {
        $admin = Admin::with(['image', 'cities'])->find($id);
        
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        return response()->json(['data' => $admin], 200);
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::find($id);
        
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:admins,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'image_id' => 'nullable|exists:media,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $admin->name = $request->name;
        }
        
        if ($request->has('email')) {
            $admin->email = $request->email;
        }
        
        if ($request->has('password')) {
            $admin->password = Hash::make($request->password);
        }
        
        if ($request->has('image_id')) {
            $admin->image_id = $request->image_id;
        }

        $admin->save();

        return response()->json(['data' => $admin, 'message' => 'Admin updated successfully'], 200);
    }

    public function destroy($id)
    {
        $admin = Admin::find($id);
        
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully'], 200);
    }
}
