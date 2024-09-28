<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\Parents_note;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ParentsNoteController extends Controller
{
    /*للاهل*/
    public function createParentNote(Request $request)
    {
        $user = auth()->user();

        $userRole = auth()->user()->role_id;
        if ($userRole !== 4 ) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'parent_note' => 'required|string',
            'homework_id' => 'required|exists:homework,id',
            'student_name' => 'required|string|max:255',
        ]);

        try {

            $parentNote = Parents_note::create([
                'parent_note' => $request->input('parent_note'),
                'homework_id' => $request->input('homework_id'),
                'user_id' => $user->id,
                'student_name' => $request->input('student_name'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parent note created successfully',
                'item' => $parentNote
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating parent note: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateParentNote(Request $request, $parent_not_id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 4) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'parent_note' => 'string|max:255',
        ]);

        try {

            $parent_not = Parents_note::find($parent_not_id);

            if (!$parent_not) {
                return response()->json([
                    'status' => false,
                    'message' => 'Parent note not found.',
                    'data' => []
                ], 404);
            }


            $parent_not->parent_note = $request->input('parent_note');
            $parent_not->save();

            return response()->json([
                'success' => true,
                'message' => 'Teacher response created successfully',
                'item' => $parent_not
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating teacher response: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getParentNotesForToday(Request $request)
    {
        $userId = auth()->user()->id;
        $userRole = auth()->user()->role_id;
        if ($userRole !== 4) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        try {

            $today = now()->toDateString();
            $parentNotes = Parents_note::where('user_id', $userId)
                ->whereDate('created_at', $today)
                ->get();

            $detailedNotes = $parentNotes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'teacher_response' => $note->teacher_response,
                    'parent_note' => $note->parent_note,
                    'created_at' => $note->created_at->format('Y-m-d'),
                    'homework' => [
                        'id' => $note->homework->id,
                        'subject' => $note->homework->Subject,
                        'lesson_name' => $note->homework->Lesson_Name,
                        'description'=>$note->homework->homework,
                    ],

                ]; });

            return response()->json([
                'success' => true,
                'message' => 'Parent notes retrieved successfully',
                'data' => $detailedNotes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving parent notes: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteParentNote(Request $request, $parent_not_id)
    {
        $userRole = auth()->user()->role_id;

        if ($userRole !== 4) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {

            $parent_not = Parents_note::find($parent_not_id);

            if (!$parent_not) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent note not found.',
                ], 404);
            }


            $parent_not->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parent note deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting parent note: ' . $e->getMessage(),
            ], 500);
        }
    }

    /*للمعلمة*/

    public function createTeacherResponse(Request $request, $parent_not_id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 3) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'teacher_response' => 'max:255',
        ]);

        try {

            $parent_not = Parents_note::find($parent_not_id);

            if (!$parent_not) {
                return response()->json([
                    'status' => false,
                    'message' => 'Parent note not found.',
                    'data' => []
                ], 404);
            }


            $parent_not->teacher_response = $request->input('teacher_response') ?? null;
            $parent_not->save();

            return response()->json([
                'success' => true,
                'message' => 'Teacher response created successfully',
                'item' => $parent_not
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating teacher response: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getParentNotes(Request $request)
    {

        $userRole = auth()->user()->role_id;
        if ($userRole !== 3) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        try {

            $today = now()->toDateString();
            $parentNotes = Parents_note::
                whereDate('created_at', $today)
                ->get();

            $detailedNotes = $parentNotes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'parent_note' => $note->parent_note,
                    'student_name' => $note->student_name,
                    'homework' => [
                        'id' => $note->homework->id,
                        'subject' => $note->homework->Subject,
                        'lesson_name' => $note->homework->Lesson_Name,
                    ],
                    'created_at' => $note->created_at->format('Y-m-d'),
                    ]; });

            return response()->json([
                'success' => true,
                'message' => 'Parent notes retrieved successfully',
                'data' => $detailedNotes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving parent notes: ' . $e->getMessage(),
            ], 500);
        }
    }

}
