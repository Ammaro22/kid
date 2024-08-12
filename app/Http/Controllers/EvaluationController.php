<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Evaluation;
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

        return response()->json(['message' => 'Evaluations create successfully']);
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



    public function deleteEvaluation(Request $request, $studentId)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 3) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $student = Student::findOrFail($studentId);

        // التحقق من أن `evaluation1` هو علاقة وليس مجموعة
        $evaluations = $student->evaluation1()->get();

        if ($evaluations->isNotEmpty()) {
            foreach ($evaluations as $evaluation) {
                // التحقق من وجود ملاحظة مرتبطة بالتقييم
                if ($evaluation->note1) {
                    // احذف الملاحظة المرتبطة بالتقييم
                    $evaluation->note1->delete();
                }

                // احذف التقييم
                $evaluation->delete();
            }

            return response()->json(['message' => 'تم حذف التقييمات والملاحظات المرتبطة بها بنجاح']);
        } else {
            return response()->json(['message' => 'لا توجد تقييمات لحذفها'], 404);
        }
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
        $day = $request->input('day');
        $month = $request->input('month');
        $year = $request->input('year');
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

    public function showEvaluationsForParentMonth(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
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
            'images' => $images->toArray(),
        ];

        if ($evaluations->isNotEmpty()) {
            return response()->json($output);
        } else {
            return response()->json(['message' => 'Evaluation not found'], 404);
        }
    }




}
