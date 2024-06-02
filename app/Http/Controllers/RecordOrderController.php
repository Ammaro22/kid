<?php

namespace App\Http\Controllers;

use App\Models\Record_order;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RecordOrderController extends Controller
{
    public function Record(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $StudentId = $request->student_id;
        $accept = $request->input('accept', false);

        $Student = Student::find($StudentId);

        if (!$Student) {
            return response()->json(['error' => 'student not found'], 404);
        }

        $recodData = [
            'student_id' => $StudentId,
            'accept' => $accept,
        ];

        $record = Record_order::create($recodData);

        return response()->json([
            'Record' => $record,

        ]);
    }

    public function acceptrecord($id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $record = Record_order::find($id);

        if (!$record) {
            return response()->json(['message' => 'invoice not found'], 404);
        }

        $record->accept = true;
        $record->save();

        return response()->json(['message' => 'Successfully']);
    }

    public function delete_record($id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $record = Record_order::find($id);

        if (!$record) {
            return response()->json([
                'status' => false,
                'msg' => 'Invoice not found'
            ]);
        }

        $student = $record->stud();
        $student->delete();

        $record->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Deleted successfully'
        ]);
    }
}
