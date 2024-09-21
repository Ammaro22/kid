<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Homework;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HomeworkController extends Controller
{

    public function store(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 3 ) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validatedData = $request->validate([
            'the_day' => 'required',
            'Subject' => 'required',
            'homework' => 'required',
            'Lesson_Name' => 'required',
            'category_id' => 'required'
        ]);

        $homework = Homework::create($validatedData);

        return response()->json([
            'message' => 'Homework created successfully.',
            'data' => $homework
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 3 ) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $homework = Homework::findOrFail($id);

        $updatedFields = $request->only('the_day', 'Subject', 'homework', 'category_id');


        if ($request->has('the_day')) {
            $homework->the_day = $updatedFields['the_day'];
        }

        if ($request->has('Subject')) {
            $homework->Subject = $updatedFields['Subject'];
        }

        if ($request->has('homework')) {
            $homework->homework = $updatedFields['homework'];
        }

        if ($request->has('Lesson_Name')) {
            $homework->homework = $updatedFields['Lesson_Name'];
        }

        if ($request->has('category_id')) {
            $homework->category_id = $updatedFields['category_id'];
        }

        $homework->save();

        return response()->json([
            'message' => 'Homework updated successfully.',
            'data' => $homework
        ]);
    }

    public function show(Request $request, $category_id)
    {
        $category = Category::find($category_id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found.',
                'data' => []
            ], 404);
        }


        $currentDate = now()->format('Y-m-d');


        $homeworks = Homework::where('category_id', $category_id)
            ->whereDate('created_at', $currentDate)
            ->orderByDesc('created_at')
            ->get();

        if ($homeworks->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No homeworks found for the specified category on the current date.',
                'data' => []
            ], 404);
        }

        $data = $homeworks->map(function ($homework) use ($category) {
            return [
                'day' => $homework->the_day,
                'subject' => $homework->Subject,
                'homework' => $homework->homework,
                'Lesson_Name' => $homework->Lesson_Name,
                'category' => $category->name,
                'date' => $homework->created_at->format('Y-m-d'),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Homeworks retrieved successfully.',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 3 ) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $homework = Homework::find($id);

        if (!$homework) {
            return response()->json([
                'status' => false,
                'message' => 'Homework not found.',
                'data' => []
            ], 404);
        }

        $homework->delete();

        return response()->json([
            'status' => true,
            'message' => 'Homework deleted successfully.',
            'data' => []
        ]);
    }

}
