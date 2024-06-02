<?php

namespace App\Http\Controllers;


use App\Models\Note;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NoteController extends Controller
{

    public function updateNoteAdmin(Request $request,$studentId)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 || $userRole !==2 ) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $idNote = $request->input('note_id');
        $note_admin = $request->input('note_admin');

        $student = Student::findOrFail($studentId);

        $note = Note::where('id', $idNote)->firstOrFail();

        $note->note_admin = $note_admin;
        $note->save();

        return response()->json(['message' => 'تم تحديث ملاحظة المشرف بنجاح.']);
    }
}
