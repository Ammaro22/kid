<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Class_Supervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ClassSupervisorController extends Controller
{
//    public function storeClassSupervisor(Request $request)
//    {
//        $userRole = auth()->user()->role_id;
//        if ($userRole !== 1 && $userRole !== 2) {
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//
//        $validatedData = $request->validate([
//            'class_name' => 'required|string',
//            'first_name' => 'required|string',
//            'last_name' => 'required|string',
//        ]);
//
//        $user = User::where('first_name', $validatedData['first_name'])
//            ->where('last_name', $validatedData['last_name'])
//            ->first();
//        $user_role=$user->role_id;
//        if (!$user) {
//            return response()->json(['error' => 'User not found'], 404);
//        }
//      else  if($user_role!=3){
//            return response()->json(['message' => 'User not teacher'], 401);
//        }
//
//        $category = Category::where('name', $validatedData['class_name'])->first();
//        if (!$category) {
//            return response()->json(['error' => 'Category not found'], 404);
//        }
//
//        $classSupervisor = Class_Supervisor::create([
//            'user_id' => $user->id,
//            'category_id' => $category->id,
//        ]);
//
//        return response()->json(['message' => 'Class Supervisor created successfully', 'data' => $classSupervisor], 201);
//    }

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

        $classSupervisor = Class_Supervisor::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        return response()->json(['message' => 'Class Supervisor created successfully', 'data' => $classSupervisor], 201);
    }

//    public function updateClassSupervisor(Request $request, $id)
//    {
//        $userRole = auth()->user()->role_id;
//        if ($userRole !== 1 && $userRole !== 2) {
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//        $validatedData = $request->validate([
//            'class_name' => 'string',
//            'first_name' => 'string',
//            'last_name' => 'string',
//        ]);
//
//        $classSupervisor = Class_Supervisor::find($id);
//        if (!$classSupervisor) {
//            return response()->json(['error' => 'Class Supervisor not found'], 404);
//        }
//
//        $user = User::where('first_name', $validatedData['first_name'])
//            ->where('last_name', $validatedData['last_name'])
//            ->first();
//        if (!$user) {
//            return response()->json(['error' => 'User not found'], 404);
//        }
//
//        $category = Category::where('name', $validatedData['class_name'])->first();
//        if (!$category) {
//            return response()->json(['error' => 'Category not found'], 404);
//        }
//
//        $classSupervisor->user_id = $user->id;
//        $classSupervisor->category_id = $category->id;
//        $classSupervisor->save();
//
//        return response()->json(['message' => 'Class Supervisor updated successfully', 'data' => $classSupervisor], 200);
//    }

    public function updateClassSupervisor(Request $request, $id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'class_name' => 'string',
            'user_id' => 'integer',
        ]);

        $classSupervisor = Class_Supervisor::find($id);
        if (!$classSupervisor) {
            return response()->json(['error' => 'Class Supervisor not found'], 404);
        }

        $user = User::find($validatedData['user_id']);
        $user_role = $user->role_id;
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }elseif ($user_role != 3) {
            return response()->json(['message' => 'User not teacher'], 401);
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

//
//    public function getClassSupervisor(Request $request)
//    {
//        // Validate the input
//        $validatedData = $request->validate([
//            'class_name' => 'required|string',
//        ]);
//
//
//        $category = Category::with('class_sav.user1')->where('name', $validatedData['class_name'])->first();
//        if (!$category) {
//            return response()->json(['error' => 'Category not found'], 404);
//        }
//
//
//        $classSupervisor = $category->class_sav->first();
//        if (!$classSupervisor) {
//            return response()->json(['data' => [
//                'user_name' => '',
//                'class_name' => '',
//            ]], 200);
//        }
//
//
//        $userData = [
//            'user_name' => $classSupervisor->user1->first_name . ' ' . $classSupervisor->user1->last_name,
//            'class_name' => $category->name,
//        ];
//
//
//        return response()->json(['data' => $userData], 200);
//    }
//

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
