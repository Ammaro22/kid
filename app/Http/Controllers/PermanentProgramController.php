<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Day_Subject;
use App\Models\Days_week;
use App\Models\Permanent_program;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PermanentProgramController extends Controller
{


    public function createProgram(Request $request, $categoryId)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $programData = $request->input('program');

        $category = Category::findOrFail($categoryId);

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid category',
            ], 400);
        }

        $program = Permanent_program::create([
            'category_id' => $categoryId,
        ]);

        foreach ($programData as $dayData) {
            $dayName = $dayData['day'];
            $subjectNames = $dayData['subjects'];

            $day = Days_week::where('name', $dayName)->first();

            if (!$day) {
                $day = Days_week::create([
                    'name' => $dayName,
                ]);
            }

            foreach ($subjectNames as $subjectName) {
                $subject = Subject::where('name', $subjectName)->first();

                if ($subject) {
                    $daySubject = Day_Subject::create([
                        'days_week_id' => $day->id,
                        'permanent_program_id' => $program->id,
                        'subject_id' => $subject->id,
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Program created successfully',
        ]);
    }



    public function updateProgramSubjects(Request $request,$category_id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $category = Category::find($category_id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $programData = $request->input('program');

        foreach ($programData as $dayData) {
            $dayName = $dayData['day'];
            $subjectNames = $dayData['subjects'];

            $day = Days_week::where('name', $dayName)->first();

            if (!$day) {
                $day = Days_week::create([
                    'name' => $dayName,
                ]);
            }

            $permanentProgram = Permanent_program::where('category_id', $category_id)->first();

            if (!$permanentProgram) {
                $permanentProgram = Permanent_program::create([
                    'category_id' => $category_id,
                ]);
            }

            $existingDaySubjects = Day_Subject::where('days_week_id', $day->id)
                ->where('permanent_program_id', $permanentProgram->id)
                ->get();

            foreach ($existingDaySubjects as $existingDaySubject) {
                $existingDaySubject->delete();
            }

            foreach ($subjectNames as $subjectName) {
                $subject = Subject::where('name', $subjectName)->first();

                if ($subject) {
                    $daySubject = new Day_Subject();
                    $daySubject->days_week_id = $day->id;
                    $daySubject->subject_id = $subject->id;
                    $daySubject->permanent_program_id = $permanentProgram->id;
                    $daySubject->save();
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Program subjects updated successfully',
        ]);
    }



    public function showProgram($categoryId)
    {
        $programs = Permanent_program::with('day_s.subje')
            ->whereHas('day_s', function ($query) use ($categoryId) {
                $query->whereHas('subje', function ($query) use ($categoryId) {
                    $query->where('category_id', $categoryId);
                });
            })
            ->get();

        if ($programs->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No programs found for the given category',
            ], 404);
        }

        $uniquePrograms = [];


        $programsByDay = [];

        foreach ($programs as $program) {
            $dayData = [];
            $days = Days_week::all();

            foreach ($days as $day) {
                $daySubjects = $day->day_ss()->where('permanent_program_id', $program->id)->get();

                foreach ($daySubjects as $daySubject) {
                    $subjectName = $daySubject->subje->name;

                    if (!isset($dayData[$day->name])) {
                        $dayData[$day->name] = [
                            'day' => $day->name,
                            'subjects' => [],
                        ];
                    }


                    $dayData[$day->name]['subjects'][] = $subjectName;
                }
            }


            foreach ($dayData as $day) {
                $programsByDay[$day['day']][] = $day;
            }
        }


        $daysOfWeek = ['الأحد', 'الأثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];

        foreach ($daysOfWeek as $day) {
            if (isset($programsByDay[$day])) {
                $uniquePrograms[] = $programsByDay[$day];
            }
        }

        return response()->json([
            'status' => 'success',
            'programs' => $uniquePrograms,
        ]);
    }




    public function deleteProgram($categoryId)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if the category exists
        $category = Category::find($categoryId);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $programs = Permanent_program::where('category_id', $categoryId)->get();

        foreach ($programs as $program) {
            $program->day_s()->delete();
        }

        Permanent_program::where('category_id', $categoryId)->delete();



        return response()->json([
            'status' => 'success',
            'message' => 'delete program successfully',
        ]);
    }
}
