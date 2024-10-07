<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Evaluation;
use App\Models\Evaluation_student;
use App\Models\Image_child;
use App\Models\Note;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{

//    public function createEvaluation(Request $request)
//    {
//        $userRole = auth()->user()->role_id;
//        if ($userRole !== 3) {
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//
//        $studentId = $request->input('student_id');
//        $subjects = $request->input('subjects');
//        $noteTeacher = $request->input('note_teacher');
//
//        $student = Student::find($studentId);
//
//        if (!$student) {
//            return response()->json(['error' => 'student not found'], 404);
//        }
//
//        $note = Note::create([
//            'student_id' => $student->id,
//            'note_teacher' => $noteTeacher
//        ]);
//
//        foreach ($subjects as $subject) {
//            $subjectName = $subject['name'];
//            $evaluationValue = $subject['evaluation'];
//            $subjectModel = Subject::where('name', $subjectName)->first();
//
//            if (!$subjectModel) {
//                return response()->json(['error' => 'subject not found'], 404);
//            }
//
//            $evaluation = new Evaluation();
//            $evaluation->student_id = $student->id;
//            $evaluation->subject_id = $subjectModel->id;
//            $evaluation->note_id = $note->id;
//            $evaluation->evaluation = $evaluationValue;
//            $evaluation->save();
//        }
//
//        return response()->json(['message' => 'Evaluations create successfully']);
//    }

    public function createEvaluation(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 3) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $studentId = $request->input('student_id');
        $subjects = $request->input('subjects');
        $noteTeacher = $request->input('note_teacher');

        $student = Student::find($studentId);

        if (!$student) {
            return response()->json(['error' => 'student not found'], 404);
        }

        $note = Note::create([
            'student_id' => $student->id,
            'note_teacher' => $noteTeacher
        ]);

        $evaluationCount = 0;
        $behaviorEvaluation = '';

        foreach ($subjects as $subject) {
            $subjectName = $subject['name'];
            $evaluationValue = $subject['evaluation'];


            if (strcasecmp($subjectName, "سلوك") == 0) {
                $behaviorEvaluation = $evaluationValue;
            }

            $subjectModel = Subject::where('name', $subjectName)->first();

            if (!$subjectModel) {
                return response()->json(['error' => 'subject not found'], 404);
            }

            $evaluation = new Evaluation();
            $evaluation->student_id = $student->id;
            $evaluation->subject_id = $subjectModel->id;
            $evaluation->note_id = $note->id;
            $evaluation->evaluation = $evaluationValue;
            $evaluation->save();


            if ($evaluationValue === "ممتاز") {
                $evaluationCount++;
            }
        }


        if ($behaviorEvaluation === "ممتاز" && $evaluationCount >= 2) {
            $finalEvaluation = "ممتاز";
        } elseif ($behaviorEvaluation === "ممتاز" && $evaluationCount == 1) {
            $finalEvaluation = "جيد جدا";
        } elseif ($behaviorEvaluation === "جيد جدا") {
            $finalEvaluation = "جيد جدا";
        } else {
            $finalEvaluation = "جيد";
        }


        Evaluation_student::create([
            'student_id' => $student->id,
            'Evaluation' => $finalEvaluation
        ]);

        return response()->json(['message' => 'Evaluations created successfully']);
    }



    public function updateEvaluation(Request $request, $studentId)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 3) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $student = Student::findOrFail($studentId);
        $note = $student->evaluation1->first()->note1;
        $subjects = $request->input('subjects');
        $noteTeacher = $request->input('note_teacher');

        // Update the note
        $note->note_teacher = $noteTeacher;
        $note->save();

        // Delete old evaluations
        $student->evaluation1()->delete();

        // Create new evaluations
        foreach ($subjects as $subject) {
            $subjectName = $subject['name'];
            $evaluationValue = $subject['evaluation'];

            $subjectModel = Subject::where('name', $subjectName)->first();

            if (!$subjectModel) {
                return response()->json(['error' => 'subject not found'], 404);
            }

            $evaluation = new Evaluation();
            $evaluation->student_id = $student->id;
            $evaluation->subject_id = $subjectModel->id;
            $evaluation->note_id = $note->id;
            $evaluation->evaluation = $evaluationValue;
            $evaluation->save();
        }

        return response()->json(['message' => 'Evaluations updated successfully']);
    }

    public function delete_Evaluation(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 3) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $studentId = $request->input('student_id');
        $day = $request->input('day');
        $month = $request->input('month');
        $year = $request->input('year');

        if (!$studentId || !$day || !$month || !$year) {
            return response()->json(['message' => 'Student ID, day, month, and year are required.'], 400);
        }

        $student = Student::find($studentId);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $date = Carbon::createFromDate($year, $month, $day)->toDateString();

        $evaluations = $student->evaluation1()
            ->whereDate('created_at', $date)
            ->get();

        $studentEvaluations = $student->Evaluation_student()
            ->whereDate('created_at', $date)
            ->get();

        try {

            if ($evaluations->isNotEmpty()) {
                foreach ($evaluations as $evaluation) {
                    if ($evaluation->note1) {
                        $evaluation->note1->delete();
                    }
                    $evaluation->delete();
                }
            }

            
            if ($studentEvaluations->isNotEmpty()) {
                foreach ($studentEvaluations as $studentEvaluation) {
                    $studentEvaluation->delete();
                }
            }

            return response()->json(['message' => 'The evaluations and their associated notes have been successfully deleted.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting evaluations.'], 500);
        }
    }

    public function getEvaluationsByStudentAndDate(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
        ]);

        $studentId = $request->input('student_id');
        $month = $request->input('month');
        $year = $request->input('year');

        $evaluations = Evaluation_student::where('student_id', $studentId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        if ($evaluations->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No evaluations found for the specified student and date.',
                'data' => []
            ], 404);
        }


        $formattedEvaluations = $evaluations->map(function ($evaluation, $index) {
            return [
                'id' => $evaluation->id,
                'Weekly_Evaluation_' . ($index + 1) => $evaluation->Evaluation,
                'student_id' => $evaluation->student_id,
                'created_at' => $evaluation->created_at->format('Y-m-d'),

            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Evaluations retrieved successfully.',
            'data' => $formattedEvaluations
        ]);
    }

    public function showEvaluations(Request $request, $studentId)
    {
        $day = $request->input('day');
        $month = $request->input('month');
        $year = $request->input('year');

        $student = Student::findOrFail($studentId);
        $evaluations = $student->evaluation1->sortBy('created_at');
        $filteredEvaluations = $evaluations->filter(function ($evaluation) use ($day, $month, $year) {
            return $evaluation->created_at->format('d') == $day && $evaluation->created_at->format('m') == $month && $evaluation->created_at->format('Y') == $year;
        });

        $noteIds = $filteredEvaluations->pluck('note_id');
        $notes = Note::whereIn('id', $noteIds)->get();

        $output = [
            'evaluation' => $filteredEvaluations->map(function ($evaluation) {
                return [
                    'id' => $evaluation->id,
                    'subject' => $evaluation->subject1->name,
                    'evaluation' => $evaluation->evaluation,
                    'created_at' => $evaluation->created_at->format('Y-m-d H:i:s'),
                ];
            })->values()->toArray(),
            'note_teacher' => $notes->toArray(),
        ];

        if ($filteredEvaluations->isNotEmpty()) {
            return response()->json($output);
        } else {
            return response()->json(['message' => 'evaluation not found'], 404);
        }
    }


    public function showEvaluationsmonth(Request $request, $studentId)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $student = Student::findOrFail($studentId);
        $evaluations = $student->evaluation1;

        $filteredEvaluations = $evaluations->filter(function ($evaluation) use ($month, $year) {
            return $evaluation->created_at->format('m') == $month && $evaluation->created_at->format('Y') == $year;
        });

        $days = $filteredEvaluations->groupBy(function ($evaluation) {
            return $evaluation->created_at->format('Y-m-d');
        })->map(function ($group, $date) {
            $dateObject = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
            return [
                'day' => $dateObject->format('d'),
                'month' => $dateObject->format('m'),
                'year' => $dateObject->format('Y')
            ];
        })->values()->toArray();

        if ($filteredEvaluations->isNotEmpty()) {
            return response()->json(['days' => $days]);
        } else {
            return response()->json([
                'message' => 'No evaluations found for the given month and year'
            ], 404);
        }
    }


    public function getEvaluationsByCategoryId($categoryId)
    {
        $category = Category::findOrFail($categoryId);


        $students = $category->stu();

        $evaluations = [];
        foreach ($students as $student) {
            $studentEvaluations = $student->evaluations;
            $evaluations = array_merge($evaluations, $studentEvaluations->toArray());
        }


        return response()->json(['evaluations' => $evaluations]);
    }

    /*عرض تقيمات الطالب بالنسبة لاهله*/




    public function showEvaluationsforparent(Request $request)
    {

        $currentDate = now();
        $day = $currentDate->format('d');
        $month = $currentDate->format('m');
        $year = $currentDate->format('Y');

        $studentName = $request->input('student_name');
        $className = $request->input('class_name');
        $userId = auth()->id();

        $student = Student::whereHas('category', function ($query) use ($className) {
            $query->where('name', $className);
        })
            ->where('name', $studentName)
            ->where('user_id', $userId)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $studentId = $student->id;

        $images = Image_child::where('student_id', $studentId)->get();

        $evaluations = $student->evaluation1->filter(function ($evaluation) use ($day, $month, $year) {
            return $evaluation->created_at->format('d') == $day &&
                $evaluation->created_at->format('m') == $month &&
                $evaluation->created_at->format('Y') == $year;
        })->values();

        $noteIds = $evaluations->pluck('note_id');
        $notes = Note::whereIn('id', $noteIds)->get();
        $note = $notes->firstWhere('id', $evaluations->first()->note_id);
        $output = [
            'student_name' => $student->name,
            'class_name' => $student->category->name,
            'note_teacher' => $note ? $note->note_teacher : null,
            'note_admin' => $note ? $note->note_admin : null,
            'evaluations' => $evaluations->map(function ($evaluation) {
                return [
                    'id' => $evaluation->id,
                    'evaluation' => $evaluation->evaluation,
                    'subject_name' => $evaluation->subject1->name,
                    'created_at' => $evaluation->created_at->format('Y-m-d'),
                ];
            })->values()->all(),
            'images' => $images->toArray()
        ];

        if ($evaluations->isNotEmpty()) {
            return response()->json($output);
        } else {
            return response()->json(['message' => 'Evaluation not found'], 404);
        }
    }

//    public function showEvaluationsForParentMonth(Request $request)
//    {
//        $currentDate = now();
//        $month = $currentDate->format('m');
//        $year = $currentDate->format('Y');
//        $studentName = $request->input('student_name');
//        $className = $request->input('class_name');
//        $userId = auth()->id();
//
//        $student = Student::whereHas('category', function ($query) use ($className) {
//            $query->where('name', $className);
//        })
//            ->where('name', $studentName)
//            ->where('user_id', $userId)
//            ->first();
//
//        if (!$student) {
//            return response()->json(['message' => 'Student not found'], 404);
//        }
//
//        $studentId = $student->id;
//        $images = Image_child::where('student_id', $studentId)->get();
//
//        $evaluations = $student->evaluation1->filter(function ($evaluation) use ($month, $year) {
//            return $evaluation->created_at->format('m') == $month && $evaluation->created_at->format('Y') == $year;
//        });
//
//        $noteIds = $evaluations->pluck('note_id');
//        $notes = Note::whereIn('id', $noteIds)->get();
//
//        $groupedEvaluations = $evaluations->groupBy(function ($evaluation) {
//            return $evaluation->created_at->format('Y-m-d');
//        });
//
//        $evaluationDaysCount = $groupedEvaluations->keys()->count();
//
//        $output = [
//            'student_name' => $student->name,
//            'class_name' => $student->category->name,
//            'evaluation_days_count' => $evaluationDaysCount,
//            'evaluations' => $groupedEvaluations->map(function ($evaluationsByDate) use ($notes) {
//                $note = $notes->firstWhere('id', $evaluationsByDate->first()->note_id);
//                return [
//                    'date' => $evaluationsByDate->first()->created_at->format('Y-m-d'),
//                    'note_teacher' => $note ? $note->note_teacher : null,
//                    'note_admin' => $note ? $note->note_admin : null,
//                    'evaluations' => $evaluationsByDate->map(function ($evaluation) {
//                        return [
//                            'id' => $evaluation->id,
//                            'evaluation' => $evaluation->evaluation,
//                            'subject_name' => $evaluation->subject1->name,
//                        ];
//                    })->values()->toArray(),
//                ];
//            })->values()->toArray(),
//            'images' => $images->toArray(),
//        ];
//
//        if ($evaluations->isNotEmpty()) {
//            return response()->json($output);
//        } else {
//            return response()->json(['message' => 'Evaluation not found'], 404);
//        }
//    }

    public function showEvaluationsForParentMonth(Request $request)
    {
        $currentDate = now();
        $month = $currentDate->format('m');
        $year = $currentDate->format('Y');
        $studentName = $request->input('student_name');
        $className = $request->input('class_name');
        $userId = auth()->id();

        $student = Student::whereHas('category', function ($query) use ($className) {
            $query->where('name', $className);
        })
            ->where('name', $studentName)
            ->where('user_id', $userId)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $studentId = $student->id;
        $images = Image_child::where('student_id', $studentId)->get();

        $evaluations = $student->evaluation1->filter(function ($evaluation) use ($month, $year) {
            return $evaluation->created_at->format('m') == $month && $evaluation->created_at->format('Y') == $year;
        });

        $noteIds = $evaluations->pluck('note_id');
        $notes = Note::whereIn('id', $noteIds)->get();

        $groupedEvaluations = $evaluations->groupBy(function ($evaluation) {
            return $evaluation->created_at->format('Y-m-d');
        });

        $evaluationDaysCount = $groupedEvaluations->keys()->count();


        $evaluationStudents = Evaluation_student::where('student_id', $studentId)->get();

        $output = [
            'student_name' => $student->name,
            'class_name' => $student->category->name,
            'evaluation_days_count' => $evaluationDaysCount,
            'evaluations' => $groupedEvaluations->map(function ($evaluationsByDate) use ($notes) {
                $note = $notes->firstWhere('id', $evaluationsByDate->first()->note_id);
                return [
                    'date' => $evaluationsByDate->first()->created_at->format('Y-m-d'),
                    'note_teacher' => $note ? $note->note_teacher : null,
                    'note_admin' => $note ? $note->note_admin : null,
                    'evaluations' => $evaluationsByDate->map(function ($evaluation) {
                        return [
                            'id' => $evaluation->id,
                            'evaluation' => $evaluation->evaluation,
                            'subject_name' => $evaluation->subject1->name,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
            'evaluation_students' => $evaluationStudents->map(function ($evaluationStudent) {
                return [
                    'id' => $evaluationStudent->id,
                    'Weekly_Evaluation' => $evaluationStudent->Evaluation,
                    'created_at' => $evaluationStudent->created_at->format('Y-m-d H:i:s'),
                ];
            })->values()->toArray(),

            'images' => $images->toArray(),

        ];

        if ($evaluations->isNotEmpty()) {
            return response()->json($output);
        } else {
            return response()->json(['message' => 'Evaluation not found'], 404);
        }
    }

    public function showEvaluationsForParentMonth2(Request $request)
    {

        $month = $request->input('month');
        $year = $request->input('year');
        $studentName = $request->input('student_name');
        $className = $request->input('class_name');
        $userId = auth()->id();


        if (!$month || !$year) {
            return response()->json(['message' => 'Please provide both month and year.'], 400);
        }


        $student = Student::whereHas('category', function ($query) use ($className) {
            $query->where('name', $className);
        })
            ->where('name', $studentName)
            ->where('user_id', $userId)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $studentId = $student->id;
        $images = Image_child::where('student_id', $studentId)->get();


        $evaluations = $student->evaluation1->filter(function ($evaluation) use ($month, $year) {
            return $evaluation->created_at->format('m') == $month && $evaluation->created_at->format('Y') == $year;
        });

        $noteIds = $evaluations->pluck('note_id');
        $notes = Note::whereIn('id', $noteIds)->get();


        $groupedEvaluations = $evaluations->groupBy(function ($evaluation) {
            return $evaluation->created_at->format('Y-m-d');
        });

        $evaluationDaysCount = $groupedEvaluations->keys()->count();
        $evaluationStudents = Evaluation_student::where('student_id', $studentId)->get();

        $output = [
            'student_name' => $student->name,
            'class_name' => $student->category->name,
            'evaluation_days_count' => $evaluationDaysCount,
            'evaluations' => $groupedEvaluations->map(function ($evaluationsByDate) use ($notes) {
                $note = $notes->firstWhere('id', $evaluationsByDate->first()->note_id);
                return [
                    'date' => $evaluationsByDate->first()->created_at->format('Y-m-d'),
                    'note_teacher' => $note ? $note->note_teacher : null,
                    'note_admin' => $note ? $note->note_admin : null,
                    'evaluations' => $evaluationsByDate->map(function ($evaluation) {
                        return [
                            'id' => $evaluation->id,
                            'evaluation' => $evaluation->evaluation,
                            'subject_name' => $evaluation->subject1->name,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
            'evaluation_students' => $evaluationStudents->map(function ($evaluationStudent) {
                return [
                    'id' => $evaluationStudent->id,
                    'Weekly_Evaluation' => $evaluationStudent->Evaluation,
                    'created_at' => $evaluationStudent->created_at->format('Y-m-d H:i:s'),
                ];
            })->values()->toArray(),
            'images' => $images->toArray(),
        ];


        if ($evaluations->isNotEmpty()) {
            return response()->json($output);
        } else {
            return response()->json(['message' => 'Evaluation not found'], 404);
        }
    }





}
