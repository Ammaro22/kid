<?php

namespace App\Http\Controllers;

use App\Models\Evaluation_Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;

class EvaluationTeacherController extends Controller
{

    public function createTeacherEvaluation(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'evaluations' => 'required|array',
            'evaluations.*.user_id' => 'required|exists:users,id',
            'evaluations.*.evaluation' => 'required|string',
            'evaluations.*.Note' => 'nullable|string',
            'evaluations.*.evaluation_criterias_id' => 'required|exists:evaluation__criterias,id',
        ]);

        $evaluationsData = $request->input('evaluations');
        $createdEvaluations = [];
        $errors = [];

        foreach ($evaluationsData as $evaluationData) {

            $existingEvaluation = Evaluation_Teacher::where('user_id', $evaluationData['user_id'])
                ->where('evaluation_criterias_id', $evaluationData['evaluation_criterias_id'])
                ->first();

            if ($existingEvaluation) {

                return response()->json([
                    'message' => 'Evaluation already exists for this user and criteria.'
                ], 201);

            }


            $evaluation = Evaluation_Teacher::create([
                'user_id' => $evaluationData['user_id'],
                'evaluation' => $evaluationData['evaluation'],
                'note' => $evaluationData['Note'],
                'evaluation_criterias_id' => $evaluationData['evaluation_criterias_id'],
            ]);

            $createdEvaluations[] = $evaluation;
        }

        return response()->json([
            'status' => true,
            'message' => 'Evaluations processed successfully.',
            'data' => $createdEvaluations,
        ], 201);
    }

    public function updateTeacherEvaluation(Request $request, $id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2 ) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'evaluation' => 'nullable|string',
            'note' => 'nullable|string',
            'evaluation_criterias_id' => 'nullable|exists:evaluation__criterias,id',
        ], [
            'evaluation.string' => 'The evaluation must be a string.',
            'note.string' => 'The note must be a string if provided.',
            'evaluation_criterias_id.exists' => 'The specified evaluation criteria do not exist.',
        ]);


        $evaluation = Evaluation_Teacher::find($id);

        if (!$evaluation) {
            return response()->json(['message' => 'Evaluation not found.'], 404);
        }


        $evaluation->fill(array_filter($validatedData));


        $evaluation->save();


        return response()->json([
            'status' => true,
            'message' => 'Evaluation updated successfully.',
            'data' => $evaluation,
        ]);
    }

    public function showTeacherEvaluations(Request $request, $id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2 && $userRole !== 3) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate that day, month, and year are provided and valid
        $request->validate([
            'day' => 'nullable|integer|between:1,31',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ], [
            'day.integer' => 'The day must be an integer.',
            'day.between' => 'The day must be between 1 and 31.',
            'month.integer' => 'The month must be an integer.',
            'month.between' => 'The month must be between 1 and 12.',
            'year.integer' => 'The year must be an integer.',
            'year.min' => 'The year must be greater than or equal to 2000.',
            'year.max' => 'The year must be less than or equal to 2100.',
        ]);

        // Query for evaluations
        $evaluationsQuery = Evaluation_Teacher::where('user_id', $id);

        // Filter based on day, month, and year if provided
        if ($request->filled('day') && $request->filled('month') && $request->filled('year')) {
            $day = $request->input('day');
            $month = $request->input('month');
            $year = $request->input('year');

            $evaluationsQuery->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereDay('created_at', $day);
        } elseif ($request->filled('month') && $request->filled('year')) {
            $month = $request->input('month');
            $year = $request->input('year');

            $evaluationsQuery->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
        } elseif ($request->filled('year')) {
            $year = $request->input('year');

            $evaluationsQuery->whereYear('created_at', $year);
        }

        $evaluations = $evaluationsQuery->get();

        if ($evaluations->isEmpty()) {
            return response()->json(['message' => 'No evaluations found for the specified teacher.'], 404);
        }

        $user = User::find($id);
        $userName = $user ? $user->first_name . ' ' . $user->last_name : 'Unknown User';

        // Mapping the evaluations with day, month, and year
        $output = $evaluations->map(function ($evaluation) {
            return [
                'id' => $evaluation->id,
                'note' => $evaluation->Note,
                'evaluation' => $evaluation->evaluation,
                'evaluation_criteria' => $evaluation->evaluation_criterias->evaluation_criterias,
                'day' => $evaluation->created_at->format('d'),  // Extract day
                'month' => $evaluation->created_at->format('F'), // Extract full month name
                'year' => $evaluation->created_at->format('Y'),  // Extract year
            ];
        });

        return response()->json([
            'status' => true,
            'user_name' => $userName,
            'evaluations' => $output,
        ]);
    }

    public function showTeacherEvaluationstoteacher(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 3) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ], [
            'month.integer' => 'The month must be an integer.',
            'month.between' => 'The month must be between 1 and 12.',
            'year.integer' => 'The year must be an integer.',
            'year.min' => 'The year must be greater than or equal to 2000.',
            'year.max' => 'The year must be less than or equal to 2100.',
        ]);


        $userId = auth()->id();

        $evaluationsQuery = Evaluation_Teacher::where('user_id', $userId);


        if ($request->filled('month') && $request->filled('year')) {
            $month = $request->input('month');
            $year = $request->input('year');

            $evaluationsQuery->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
        }

        $evaluations = $evaluationsQuery->get();

        if ($evaluations->isEmpty()) {
            return response()->json(['message' => 'No evaluations found for the specified teacher.'], 404);
        }
        $user = User::find($userId);
        $userName = $user ? $user->first_name . ' ' . $user->last_name : 'Unknown User';
        $output = $evaluations->map(function ($evaluation) {
            return [
                'id' => $evaluation->id,
                'note' => $evaluation->Note,
                'evaluation' => $evaluation->evaluation,
                'evaluation_criteria' => $evaluation->evaluation_criterias->evaluation_criterias,
                'created_at' => $evaluation->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => true,
            'user_id' => $userName,
            'evaluations' => $output,
        ]);
    }

    public function deleteTeacherEvaluation(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId = $request->input('user_id');
        $day = $request->input('day');
        $month = $request->input('month');
        $year = $request->input('year');


        if (!$userId || !$day || !$month || !$year) {
            return response()->json(['message' => 'User ID, day, month, and year are required.'], 400);
        }

        try {

            $date = Carbon::createFromDate($year, $month, $day)->toDateString();


            $deletedCount = Evaluation_Teacher::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->delete();

            if ($deletedCount === 0) {
                return response()->json(['message' => 'No evaluations found for the specified user and date.'], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Evaluations deleted successfully.',

            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting evaluations: ' . $e->getMessage()], 500);
        }
    }

    public function getTeacherEvaluationsByYear(Request $request, $teacherId)
    {

        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $userRole = $user->role_id;
        if ($userRole !== 1 && $userRole !== 2 && $userRole !== 3) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year = $request->input('year');


        $evaluations = Evaluation_Teacher::where('user_id', $teacherId)
            ->whereYear('created_at', $year)
            ->orderBy('created_at')
            ->get();


        $evaluationsGroupedByDate = $evaluations->groupBy(function ($evaluation) {
            return $evaluation->created_at->format('Y-m-d');
        });

        $result = [];
        foreach ($evaluationsGroupedByDate as $date => $evaluationsOnDate) {
            $chunks = $evaluationsOnDate->chunk(4);
            foreach ($chunks as $chunk) {
                $monthName = $chunk->first()->created_at->format('F');
                $result[] = [
                    'month' => $monthName,
                    'date' => $date,
                ];
            }
        }

        if (empty($result)) {
            return response()->json(['message' => 'No evaluations found for the specified teacher in the given year.'], 404);
        }

        return response()->json([
            'status' => true,
            'evaluations' => $result,
        ]);
    }
}
