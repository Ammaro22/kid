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


        foreach ($programData as $dayData) {
            $dayName = $dayData['day'];
            $subjectNames = $dayData['subjects'];

            $day = Days_week::where('name', $dayName)->first();

            if (!$day) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'day not found',
                ]);
            }

            foreach ($subjectNames as $subjectName) {
                $subject = Subject::where('name', $subjectName)->first();
                if (!$subject) {

                    return response()->json([
                        'status' => 'error',
                        'message' => 'supject not found',
                    ]);

                }

                if ($subject) {
                    $daySubject = Day_Subject::create([
                        'days_week_id' => $day->id,
                        'category_id' => $category->id,
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



//    public function updateProgram(Request $request, $categoryId)
//    {
//        $userRole = auth()->user()->role_id;
//        if ($userRole !== 1 && $userRole !== 2) {
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//
//        $newProgram = $request->input('program');
//
//        $category = Category::findOrFail($categoryId);
//
//        if (!$category) {
//            return response()->json([
//                'status' => 'error',
//                'message' => 'Invalid category',
//            ], 400);
//        }
//
//        foreach ($newProgram as $dayData) {
//            $dayName = $dayData['day'];
//            $subjectNames = $dayData['subjects'];
//
//            $day = Days_week::where('name', $dayName)->first();
//
//            if (!$day) {
//                return response()->json([
//                    'status' => 'error',
//                    'message' => 'Day not found',
//                ]);
//            }
//
//            foreach ($subjectNames as $subjectName) {
//                $subject = Subject::where('name', $subjectName)->first();
//                if (!$subject) {
//                    return response()->json([
//                        'status' => 'error',
//                        'message' => 'Subject not found',
//                    ]);
//                }
//
//                $daySubject = Day_Subject::create([
//                    'days_week_id' => $day->id,
//                    'category_id' => $category->id,
//                    'subject_id' => $subject->id,
//                ]);
//            }
//        }
//
//        return response()->json([
//            'status' => 'success',
//            'message' => 'Program updated successfully',
//        ]);
//    }

    public function updateProgram(Request $request, $categoryId)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $newProgram = $request->input('program');

        $category = Category::findOrFail($categoryId);

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid category',
            ], 400);
        }

        // First, delete all old program days
        $oldDaySubjects = Day_Subject::where('category_id', $categoryId)->get();
        foreach ($oldDaySubjects as $oldDaySubject) {
            $oldDaySubject->delete();
        }

        // Now, store the new program
        foreach ($newProgram as $dayData) {
            $dayName = $dayData['day'];
            $subjectNames = $dayData['subjects'];

            $day = Days_week::where('name', $dayName)->first();

            if (!$day) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Day not found',
                ]);
            }

            foreach ($subjectNames as $subjectName) {
                $subject = Subject::where('name', $subjectName)->first();
                if (!$subject) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Subject not found',
                    ]);
                }

                $daySubject = Day_Subject::create([
                    'days_week_id' => $day->id,
                    'category_id' => $category->id,
                    'subject_id' => $subject->id,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Program updated successfully',
        ]);
    }

//    public function showProgram($categoryId)
//    {
//        $programs = Day_Subject::with('subje')
//            ->whereHas('day_ss', function ($query) use ($categoryId) {
//                $query->whereHas('subje', function ($query) use ($categoryId) {
//                    $query->where('category_id', $categoryId);
//                });
//            })
//            ->get();
//
//        if ($programs->isEmpty()) {
//            return response()->json([
//                'status' => 'error',
//                'message' => 'No programs found for the given category',
//            ], 404);
//        }
//
//        $uniquePrograms = [];
//
//
//        $programsByDay = [];
//
//        foreach ($programs as $program) {
//            $dayData = [];
//            $days = Days_week::all();
//
//            foreach ($days as $day) {
//                $daySubjects = $day->day_ss()->where('permanent_program_id', $program->id)->get();
//
//                foreach ($daySubjects as $daySubject) {
//                    $subjectName = $daySubject->subje->name;
//
//                    if (!isset($dayData[$day->name])) {
//                        $dayData[$day->name] = [
//                            'day' => $day->name,
//                            'subjects' => [],
//                        ];
//                    }
//
//
//                    $dayData[$day->name]['subjects'][] = $subjectName;
//                }
//            }
//
//
//            foreach ($dayData as $day) {
//                $programsByDay[$day['day']][] = $day;
//            }
//        }
//
//
//        $daysOfWeek = ['الأحد', 'الأثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
//
//        foreach ($daysOfWeek as $day) {
//            if (isset($programsByDay[$day])) {
//                $uniquePrograms[] = $programsByDay[$day];
//            }
//        }
//
//        return response()->json([
//            'status' => 'success',
//            'programs' => $uniquePrograms,
//        ]);
//    }

    public function showProgram($categoryId) {
        $programs = Day_Subject::with(['subje', 'days_w'])
            ->whereHas('cat', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->get();

        if ($programs->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No programs found for the given category',
            ], 404);
        }

        $programsByDay = [];

        foreach ($programs as $program) {
            $dayName = $program->days_w->name;
            $subjectName = $program->subje->name;

            if (!isset($programsByDay[$dayName])) {
                $programsByDay[$dayName] = [
                    'day' => $dayName,
                    'subjects' => [],
                ];
            }

            $programsByDay[$dayName]['subjects'][] = $subjectName;
        }

        $uniquePrograms = [];

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


        $category = Category::find($categoryId);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $programs = Day_Subject::where('category_id', $categoryId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'delete program successfully',
        ]);
    }

}
