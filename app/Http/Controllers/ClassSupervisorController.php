<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Class_Supervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ClassSupervisorController extends Controller
{
    public function storeClassSupervisor(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'class_name' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        $user = User::find($validatedData['user_id']);
        $user_role = $user->role_id;
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        } elseif ($user_role != 3) {
            return response()->json(['message' => 'User not teacher'], 401);
        }

        $category = Category::where('name', $validatedData['class_name'])->first();
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Check if a Class_Supervisor record already exists for the given user_id and category_id
        $existingClassSupervisor = Class_Supervisor::where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->first();

        if ($existingClassSupervisor) {
            return response()->json(['message' => 'Class Supervisor already exists'], 400);
        }

        $classSupervisor = Class_Supervisor::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        return response()->json(['message' => 'Class Supervisor created successfully', 'data' => $classSupervisor], 201);
    }

    public function updateClassSupervisor(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'class_name' => 'string',
            'user_id' => 'integer',
        ]);

        $user = User::find($validatedData['user_id']);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $user_role = $user->role_id;
        if ($user_role != 3) {
            return response()->json(['message' => 'User not teacher'], 401);
        }

        $classSupervisor = Class_Supervisor::where('user_id', $validatedData['user_id'])->first();
        if (!$classSupervisor) {
            return response()->json(['error' => 'Class Supervisor not found'], 404);
        }

        $existingClassSupervisor = Class_Supervisor::where('user_id', $validatedData['user_id'])
            ->where('category_id', $classSupervisor->category_id)
            ->where('id', '!=', $classSupervisor->id)
            ->first();

        if ($existingClassSupervisor) {
            return response()->json(['message' => 'Class Supervisor already exists'], 400);
        }

        $category = Category::where('name', $validatedData['class_name'])->first();
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $classSupervisor->user_id = $user->id;
        $classSupervisor->category_id = $category->id;
        $classSupervisor->save();

        return response()->json(['message' => 'Class Supervisor updated successfully', 'data' => $classSupervisor], 200);
    }

    public function getClassBySupervisorId(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $supervisor = User::find($validatedData['user_id']);

        if (!$supervisor) {
            return response()->json(['error' => 'Supervisor not found'], 404);
        }

        $category = Category::whereHas('class_sav', function ($query) use ($supervisor) {
            $query->where('user_id', $supervisor->id);
        })->first();

        if (!$category) {
            return response()->json(['data' => [
                'error'=>'Theres no row for this goose',
                'class_name' => '',
            ]], 200);
        }

        $classData = [
            'class_name' => $category->name,
        ];

        return response()->json(['data' => $classData], 200);
    }

    public function deleteClassSupervisor($id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $classSupervisor = Class_Supervisor::find($id);
        if (!$classSupervisor) {
            return response()->json(['error' => 'Class Supervisor not found'], 404);
        }

        $classSupervisor->delete();

        return response()->json(['message' => 'Class Supervisor deleted successfully'], 200);
    }
}
