<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Image_child;
use App\Models\Image_student;
use App\Models\Record_order;
use App\Models\Student;
use App\Models\Student_before_accept;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RecordOrderController extends Controller
{
    public function Record(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 4) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $StudentId = $request->student_id;
        $accept = $request->input('accept', false);

        $Student = Student_before_accept::find($StudentId);

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
            abort(403, 'Unauthorized access');
        }

        $record = Record_order::find($id);
        if (!$record) {
            abort(404, 'Record order not found');
        }

        $studentBeforeAccept = Student_before_accept::find($record->student_id);
        if (!$studentBeforeAccept) {
            abort(404, 'Student record not found');
        }

        $student = $record->student1;
        if (!$student) {
            $student = new Student();
            $student->fill($studentBeforeAccept->toArray());
            $student->record_order_id = $record->id;
            $student->save();

            // Transfer images from image_student to image_children
            foreach ($studentBeforeAccept->image_s as $image) {
                $student->image_c()->create([
                    'name' => $image->name,
                    'path' => $image->path,
                    'student_id' => $student->id,
                ]);
            }
        } else {
            $student->update($studentBeforeAccept->toArray());

            // Transfer images from image_student to image_children
            foreach ($studentBeforeAccept->image_s as $image) {
                $student->image_c()->create([
                    'name' => $image->name,
                    'path' => $image->path,
                    'student_id' => $student->id,
                ]);
            }
        }

        $record->accept = true;
        $record->save();

        return response()->json(['message' => 'Successfully accepted record']);
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

    public function showAllRecords()
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $records = Record_order::where('accept', 0)->get();

        if ($records->isEmpty()) {
            return response()->json(['message' => 'No records found'], 404);
        }

        $recordsData = [];
        foreach ($records as $record) {
            $student = Student_before_accept::find($record->student_id);
            $studentName = $student ? $student->name : 'Unknown';
            $studentfName = $student ? $student->name_father : 'Unknown';
            $studentmName = $student ? $student->name_mother : 'Unknown';

            $category = Category::find($student->category_id);
            $categoryName = $category ? $category->name : 'Unknown';

            $recordData = [
                'id' => $record->id,
                'student_name' => $studentName,
                'father_name'=>$studentfName,
                'mother_name'=>$studentmName,
                'category_name' => $categoryName,
                'created_at' => $record->created_at,
            ];

            $recordsData[] = $recordData;
        }

        return response()->json([
            'records' => $recordsData,
        ], 200);
    }

    public function showRecordDetails($record_order_id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $record = Record_order::find($record_order_id);
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $student = Student_before_accept::find($record->student_id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $category = Category::find($student->category_id);
        $categoryName = $category ? $category->name : 'Unknown';
        $studentImages = Image_student::where('student_id', $student->id)->get();
        $recordData = [
            'id' => $record->id,
            'student_name' => $student->name,
            'date_birth' => $student->date_birth,
            'gender' => $student->gender,
            'place_birth' => $student->place_birth,
            'number_brother' => $student->number_brother,
            'arrangement_in_family' => $student->arrangement_in_family,
            'name_father' => $student->name_father,
            'name_mother' => $student->name_mother,
            'father_academic_qualification' => $student->father_academic_qualification,
            'mother_academic_qualification' => $student->mother_academic_qualification,
            'father_work' => $student->father_work,
            'mother_work' => $student->mother_work,
            'home_address' => $student->home_address,
            'father_phone' => $student->father_phone,
            'mother_phone' => $student->mother_phone,
            'landline_phone' => $student->landline_phone,
            'chronic_diseases' => $student->chronic_diseases,
            'type_allergies' => $student->type_allergies,
            'medicines_for_child' => $student->medicines_for_child,
            'dealing_with_heat' => $student->dealing_with_heat,
            'preferred_name' => $student->preferred_name,
            'favorite_color' => $student->favorite_color,
            'favorite_game' => $student->favorite_game,
            'favorite_meal' => $student->favorite_meal,
            'daytime_bedtime' => $student->daytime_bedtime,
            'night_sleep_time' => $student->night_sleep_time,
            'relationship_with_strangers' => $student->relationship_with_strangers,
            'relationship_with_children' => $student->relationship_with_children,
            'person_responsible_for_receiving' => $student->person_responsible_for_receiving,
            'person_who_fills_the_form' => $student->person_who_fills_the_form,
            'photo_family_book' => $student->photo_family_book,
            'photo_father_page' => $student->photo_father_page,
            'photo_mother_page' => $student->photo_mother_page,
            'photo_child_page' => $student->photo_child_page,
            'photo_father_identity' => $student->photo_father_identity,
            'photo_mother_identity' => $student->photo_mother_identity,
            'photo_vaccine_card' => $student->photo_vaccine_card,
            'category_name' => $categoryName,
            'date' => $record->created_at->format('Y-m-d'),
            'images' => $studentImages->map(function ($image) {
                return [
                    'id' => $image->id,
                    'name'=>$image->name,
                    'path' => $image->path,
                ];
            })->toArray(),
        ];

        return response()->json([
            'record' => $recordData,
        ], 200);
    }

    public function showRecords()
    {

        $userRole = auth()->user()->role_id;
        if ($userRole !== 4) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $userId = auth()->user()->id;

        $records = Record_order::with(['stud' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->select('id', 'student_id', 'accept','created_at')
            ->get();

        $response = [];
        foreach ($records as $record) {
            if ($record->stud) {
                $status = '';
                if ($record->accept == 0) {
                    $status = 'Not Accepted';
                } elseif ($record->accept == 1) {
                    $status = 'Accepted';
                }
                $response[] = [
                    'id' => $record->id,
                    'student_id' => $record->student_id,
                    'student_name' => $record->stud->name,
                    'status' => $status,
                    'created_at' => $record->created_at->format('Y-m-d'),
                ];
            }
        }

        if (count($response) === 0) {
            return response()->json([
                'message' => 'Your request has been rejected'
            ], 404);
        }

        return response()->json($response);
    }

}
