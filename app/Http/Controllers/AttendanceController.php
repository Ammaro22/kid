<?php

namespace App\Http\Controllers;
use App\Models\Image_child;
use App\Models\Student;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceT;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    ///////////////////// حضور الطالب//////////////////////



    public function add(Request $request)
    {

        if (!Auth::check()) {
            return response()->json(['message' => 'You are not authorized to do this'], 403);
        }


        if (Auth::user()->role_id != 3) {
            return response()->json(['message' => 'You are not authorized to do this'], 403);
        }


        $validatedData = $request->validate([
            'attendance' => 'required|array|min:1',
            'attendance.*.student_id' => 'required|integer|exists:students,id',
            'attendance.*.status' => 'required|in:Present,Absent',
        ]);

        $attendance = [];



        foreach ($validatedData['attendance'] as $attendanceItem) {
            $attendance[] = [
                'user_id' => Auth::id(),
                'student_id' => $attendanceItem['student_id'],
                'the_date' => now(),
                'status' => $attendanceItem['status'],
            ];
        }


        Attendance::insert($attendance);


        return response()->json(['message' => 'Attendance updated successfully'], 200);
    }

    public function getAllStudentAttendance(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        try {
            $theDate = now()->toDateString();

            $attendances = Attendance::whereDate('the_date', $theDate)
                ->with('student')
                ->get();

            if ($attendances->isEmpty()) {
                return response()->json(['message' => 'No attendance records found for the current date.'], 404);
            }

            $studentAttendance = $attendances->map(function ($attendance) {
                $student = $attendance->student;
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'category_name' => $student->category->name,
                    'status' => $attendance->status,
                ];
            });

            return response()->json(['students'=>[$studentAttendance]]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching student attendance.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getStudentAttendance(Request $request, $student_id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        try {
            $theDate = now()->toDateString();

            $attendance = Attendance::whereDate('the_date', $theDate)
                ->where('student_id', $student_id)
                ->with('student', 'student.category')
                ->first();

            if (!$attendance) {
                return response()->json(['message' => 'No attendance record found for the given student on the current date.'], 404);
            }

            $student = $attendance->student;
            $data = [
                'id' => $student->id,
                'name' => $student->name,
                'category_name' => $student->category->name,
                'status' => $attendance->status,
            ];

            return response()->json(['student'=>[$data]]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching student attendance.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getStudentAttendanceHistory(Request $request, $student_id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        try {
            $attendance = Attendance::where('student_id', $student_id)
                ->with('student', 'student.category')
                ->get();

            if ($attendance->isEmpty()) {
                return response()->json(['message' => 'No attendance records found for the given student.'], 404);
            }

            $student = $attendance->first()->student;
            $studentData = [
                'id' => $student->id,
                'name' => $student->name,
                'category_name' => $student->category->name,
            ];

            $attendanceHistory = $attendance->map(function ($record) {
                return [
                    'the_date' => $record->the_date,
                    'status' => $record->status,
                ];
            });

            return response()->json([
                'student' => $studentData,
                'student_attendance_history' => $attendanceHistory
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching student attendance history.', 'error' => $e->getMessage()], 500);
        }
    }

    /* عرض حضور الطالب بالنسبة لاهله*/

    public function getmyStudentAttendanceHistoryday(Request $request)
    {
        try {
            $user = $request->user();
            $studentName = $request->input('student_name');
            $theDate = $request->input('the_date');

            $student = $user->Student()->where('name', 'like', "%$studentName%")
                ->first();

            if (!$student) {
                return response()->json(['message' => 'No student record found for the given student name.'], 404);
            }

            $attendance = $student->attendance()
                ->where('the_date', $theDate)
                ->get();

            if ($attendance->isEmpty()) {
                return response()->json(['message' => 'No attendance records found for the given student and date.'], 404);
            }


            $attendanceHistory = $attendance->map(function ($record) {
                return [
                    'the_date' => $record->the_date,
                    'status' => $record->status,
                ];
            });

            $studentData = [
                'name' => $student->name,
                'category_name' => $student->category->name,
                'images' => $student->image_c()->get()->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'name'=>$image->name,
                        'path' => $image->path, ]; }),
            ];

            return response()->json([
                'student' => $studentData,
                'student_attendance_history' => $attendanceHistory
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching student attendance history.', 'error' => $e->getMessage()], 500);
        }
    }


    public function getmyStudentAttendanceHistorymonth(Request $request)
    {
        try {
            $user = $request->user();
            $studentName = $request->input('student_name');
            $theDate = $request->input('the_date');

            if (!$theDate) {
                return response()->json(['message' => 'Please provide a valid date.'], 400);
            }

            try {
                $date = Carbon::parse($theDate);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Please provide a valid date in the format YYYY-MM.'], 400);
            }

            $student = $user->Student()->where('name', 'like', "%$studentName%")
                ->first();

            if (!$student) {
                return response()->json(['message' => 'No student record found for the given student name.'], 404);
            }

            $attendance = $student->attendance()
                ->whereMonth('the_date', '=', $date->month)
                ->whereYear('the_date', '=', $date->year)
                ->get();

            if ($attendance->isEmpty()) {
                return response()->json(['message' => 'No attendance records found for the given student and date.'], 404);
            }

            $attendanceHistory = $attendance->map(function ($record) {
                return [
                    'the_date' => Carbon::parse($record->the_date)->format('Y-m'),
                    'status' => $record->status,
                ];
            });

            $studentData = [
                'name' => $student->name,
                'category_name' => $student->category->name,
                'images' => $student->image_c()->get()->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'name'=>$image->name,
                        'path' => $image->path, ]; }),
            ];

            return response()->json([
                'student' => $studentData,
                'student_attendance_history' => $attendanceHistory
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching student attendance history.', 'error' => $e->getMessage()], 500);
        }
    }

    ////////////////////////////////المعلمات////////////////////////////////

    public function makeAttendance()
    {
        if (Auth::user()->role_id == 3) {
            AttendanceT::create([
                'user_id' => Auth::id(),
                'the_date' => now(),
                'present' => true,
            ]);
            return response()->json(['success' => true, 'message' => 'Attendance marked successfully.'], 201);
        }
        else{
            return response()->json(['error' => true, 'message' => 'you are not authorized to do this'], 201);
        }
    }

    ///////////////////////////////

    public function generateQrCode()
    {

        $url = route('mark-attendance');
        $options = new QROptions();

        $options->version             = 9;
        $options->scale               = 6;
        $options->outputBase64        = false;
        $options->bgColor             = [200, 150, 200];
        $options->imageTransparent    = true;
        $options->keepAsSquare        = [
            QRMatrix::M_FINDER_DARK,
            QRMatrix::M_FINDER_DOT,
        ];

        $options->addQuietzone    = true;


        $qrcode = (new QRCode($options));
        $qrcode->addByteSegment($url);
        $qrOutputInterface = (new QRGdImagePNG($options, $qrcode->getQRMatrix()))->dump();

        $disk = 'public';
        $filePath = 'qr/' . time() . '.png';

        // Store the file using Laravel's Storage facade
        Storage::disk($disk)->put($filePath, $qrOutputInterface);

        return response()->json([
            'qr_code_path' => Storage::disk($disk)->url($filePath),// URL to the saved QR code image
        ], 200);
    }

    ///////////////////////////////

    public function getAllAttendance()
    {
        // التأكد من أن المستخدم له صلاحية المديرة أو المساعدة
        if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2) {
            // الحصول على جميع سجلات الحضور
            $attendances = AttendanceT::with('user')->whereHas('user', function ($query) {
                $query->where('role_id', 3); // تأكد من أن المستخدمين هم معلمات
            })->get();

            return response()->json(['success' => true, 'attendances' => $attendances], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }
    }

}

