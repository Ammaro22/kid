<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Image_child;
use App\Models\Student;
use App\Models\Student_before_accept;
use App\Models\User;
use App\Traits\Imageable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $userRole = $user->role_id;;
        if ($userRole !== 4) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'name' => 'required',
            'date_birth' => 'required',
            'gender' => 'required',
            'place_birth' => 'required',
            'number_brother' => 'required',
            'arrangement_in_family' => 'required',
            'name_father' => 'required',
            'name_mother' => 'required',
            'father_academic_qualification' => 'required',
            'mother_academic_qualification' => 'required',
            'father_work' => 'required',
            'mother_work' => 'required',
            'home_address' => 'required',
            'father_phone' => 'required',
            'mother_phone' => 'required',
            'landline_phone' => 'required',
            'chronic_diseases' => 'required',
            'type_allergies' => 'required',
            'medicines_for_child' => 'required',
            'dealing_with_heat' => 'required',
            'preferred_name' => 'required',
            'favorite_color' => 'required',
            'favorite_game' => 'required',
            'favorite_meal' => 'required',
            'daytime_bedtime' => 'required',
            'night_sleep_time' => 'required',
            'relationship_with_strangers' => 'required',
            'relationship_with_children' => 'required',
            'person_responsible_for_receiving' => 'required',
            'person_who_fills_the_form' => 'required',
            'photo_family_book' => 'required',
            'photo_father_page' => 'required',
            'photo_mother_page' => 'required',
            'photo_child_page' => 'required',
            'photo_father_identity' => 'required',
            'photo_mother_identity' => 'required',
            'photo_vaccine_card' => 'required',
            'category_id' => 'required',

        ]);
        $validatedData['user_id'] = $user->id;

        $photoPaths = [];
        $photoFields = [
            'photo_family_book',
            'photo_father_page',
            'photo_mother_page',
            'photo_child_page',
            'photo_father_identity',
            'photo_mother_identity',
            'photo_vaccine_card',
        ];

        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                $image = $request->file($field);
                $newImage = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('upload'), $newImage);
                $path = "upload/$newImage";
                $photoPaths[$field] = $path;
            }
        }

        $studentData = array_merge($validatedData, $photoPaths);

        $student = Student_before_accept::create($studentData);

        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $savedImages = Imageable::ssave_m($files, $student->id);
        }

        return response()->json([
            'message' => 'Student information successfully registered',
            'student' => $student
        ]);
    }

    public function update(Request $request, $id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2 && $userRole !== 4) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $student = Student::findOrFail($id);

        $student->fill($request->only([
            'name',
            'date_birth',
            'gender',
            'place_birth',
            'number_brother',
            'arrangement_in_family',
            'name_father',
            'name_mother',
            'father_academic_qualification',
            'mother_academic_qualification',
            'father_work',
            'mother_work',
            'home_address',
            'father_phone',
            'mother_phone',
            'landline_phone',
            'chronic_diseases',
            'type_allergies',
            'medicines_for_child',
            'dealing_with_heat',
            'preferred_name',
            'favorite_color',
            'favorite_game',
            'favorite_meal',
            'daytime_bedtime',
            'night_sleep_time',
            'relationship_with_strangers',
            'relationship_with_children',
            'person_responsible_for_receiving',
            'person_who_fills_the_form',
            'category_id',
        ]));


        $photoFields = [
            'photo_family_book',
            'photo_father_page',
            'photo_mother_page',
            'photo_child_page',
            'photo_father_identity',
            'photo_mother_identity',
            'photo_vaccine_card',
        ];

        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                $image = $request->file($field);
                $newImage = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('upload'), $newImage);
                $path = "upload/$newImage";
                $student->$field = $path;
            }
        }

        $student->save();



        if ($request->hasFile('images')) {
            $files = $request->file('images');

            $student = $student ?? new Student();
            $studentImages = $student->image_c()->where('student_id', $student->id)->get();

            foreach ($studentImages as $image) {
                Storage::delete($image->path);
                $image->delete();
            }

            $savedImages = Imageable::ssave_c($files, $student->id);
        }

        return response()->json([
            'message' => 'Student information has been successfully updated',
            'student' => $student
        ]);
    }

    public function showStudent($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $user = User::find($student->user_id);
        $name_user = $user ? $user->first_name . ' ' . $user->last_name : null;

        $studentData = [
            'id' => $student->id,
            'name' => $student->name,
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
            'category_id' => $student->category_id,
            'user_name' => $name_user,
            'date' => $student->created_at->format('Y-m-d'),

        ];
        $images = Image_child::where('student_id', $id)->get();
        return response()->json(['studentData'=>$studentData,
            'images' => $images],
        200);
    }

    public function showStudentsbycategory($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $students = Student::where('category_id', $categoryId)->get();

        if ($students->isEmpty()) {
            return response()->json(['message' => 'No students found for this category'], 404);
        }

        $studentsData = [];
        foreach ($students as $student) {
            $user = User::find($student->user_id);
            $nameUser = $user ? $user->first_name . ' ' . $user->last_name : null;

            $studentImages = Image_child::where('student_id', $student->id)->get();

            $studentData = [
                'id' => $student->id,
                'name' => $student->name,
                'gender' => $student->gender,
                'name_mother' => $student->name_mother,
                'name_father' => $student->name_father,
                'date_birth' => $student->date_birth,
                'user_name' => $nameUser,
                'category_id' => $student->category_id,
                'images' => $studentImages->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'name' => $image->name,
                        'path' => $image->path,
                    ];
                })->toArray(),
            ];

            $studentsData[] = $studentData;
        }

        return response()->json([
            'studentsData' => $studentsData,
        ], 200);
    }

    public function destroy($id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $student = Student::findOrFail($id);

        $images = $student->image_c;
        foreach ($images as $image) {

            $image->delete();
        }

        $student->delete();

        return response()->json(['message' => 'student deleted successfully'], 200);
    }

    public function searchStudents(Request $request)
    {
        $query = $request->input('name_student');
        $categoryName = $request->input('category_name');

        $students = Student::query();

        if ($query) {
            $students->where('name', 'like', '%' . $query . '%');
        }

        if ($categoryName) {
            $students->whereHas('category', function ($query) use ($categoryName) {
                $query->where('name', 'like', '%' . $categoryName . '%');
            });
        }

        $students = $students->with(['category', 'User'])->get();

        $searchResults = [];

        foreach ($students as $student) {
            $searchResults[] = [
                'id' => $student->id,
                'name' => $student->name,
                'date_birth' => $student->date_birth,
                'gender' => $student->gender,
                'name_father' => $student->name_father,
                'name_mother' => $student->name_mother,
                'category_name' => $student->category->name,
            ];
        }

        if (count($searchResults) > 0) {
            $student_id = $searchResults[0]['id'];
            $images = Image_child::where('student_id', $student_id)->get();

            return response()->json([
                'searchResults' => $searchResults,
                'images' => $images
            ], 200);
        } else {
            return response()->json([
                'message' => 'No students found'
            ], 404);
        }
    }

    public function getStudentCountByCategory(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2 ) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $year = $request->input('year', date('Y'));

        $studentCountByCategory = Category::withCount(['stu' => function ($query) use ($year) {
            $query->whereYear('created_at', $year);
        }])
            ->get()
            ->mapWithKeys(function ($category) {
                return [$category->name => $category->stu_count];
            });

        return response()->json([
            'student_count_by_category' => $studentCountByCategory,
            'year' => $year
        ]);
    }

    public function searchStudents2(Request $request)
    {
        $query = $request->input('name_student');

        $students = Student::query()
            ->where('name', 'like', '%' . $query . '%')
            ->with(['category', 'User'])
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'error' => 'No students found with the provided name.',
            ], 404);
        }

        $searchResults = [];

        foreach ($students as $student) {
            $searchResults[] = [
                'id' => $student->id,
                'name' => $student->name,
                'name_father' => $student->name_father,
                'category_name' => $student->category->name,
            ];
        }

        return response()->json([
            'searchResults' => $searchResults,
        ], 200);
    }

    /*عرض معلومات الطفل لاهله*/

    public function showStudentforparent(Request $request)
    {
        $userId = $request->user()->id;

        $students = Student::where('user_id', $userId)
            ->with('category')
            ->select('id', 'name', 'category_id')
            ->get();

        $data = $students->map(function ($student) {
            $images = Image_child::where('student_id', $student->id)->get();
            return [
                'id' => $student->id,
                'name' => $student->name,
                'category' => $student->category->name,
                'images' => $images
            ];
        });

        return response()->json([
            'students' => $data
        ], 200);
    }

}
