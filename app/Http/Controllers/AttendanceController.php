<?php

namespace App\Http\Controllers;
use App\Models\File;
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

    public function getmyStudentAttendanceHistorydaynow(Request $request)
    {
        try {
            $user = $request->user();
            $studentName = $request->input('student_name');
            $theDate = now()->format('Y-m-d');

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

    public function getmyStudentAttendanceHistoryday(Request $request)
    {
        try {
            $user = $request->user();
            $studentName = $request->input('student_name');

            // الحصول على المدخلات الخاصة باليوم والشهر والسنة
            $day = $request->input('day');
            $month = $request->input('month');
            $year = $request->input('year');

            // التحقق من أن جميع المدخلات موجودة
            if (!$day || !$month || !$year) {
                return response()->json(['message' => 'Day, month, and year are required.'], 400);
            }

            // إنشاء التاريخ من المدخلات
            $theDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');

            // البحث عن الطالب
            $student = $user->Student()->where('name', 'like', "%$studentName%")->first();

            if (!$student) {
                return response()->json(['message' => 'No student record found for the given student name.'], 404);
            }

            // الحصول على سجلات الحضور
            $attendance = $student->attendance()->where('the_date', $theDate)->get();

            if ($attendance->isEmpty()) {
                return response()->json(['message' => 'No attendance records found for the given student and date.'], 404);
            }

            // معالجة سجلات الحضور
            $attendanceHistory = $attendance->map(function ($record) {
                return [
                    'the_date' => $record->the_date,
                    'status' => $record->status,
                ];
            });

            // بيانات الطالب
            $studentData = [
                'name' => $student->name,
                'category_name' => $student->category->name,
                'images' => $student->image_c()->get()->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'name' => $image->name,
                        'path' => $image->path,
                    ];
                }),
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
            $theDate = now()->format('Y-m');

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
                    'the_date' => Carbon::parse($record->the_date)->format('Y-m-d'),
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

    public function getmyStudentAttendanceHistorymonth2(Request $request)
    {
        try {
            $user = $request->user();
            $studentName = $request->input('student_name');
            $month = $request->input('month');
            $year = $request->input('year');


            if ( !$month || !$year) {
                return response()->json(['message' => 'Please provide a valid day, month, and year.'], 400);
            }

            try {

                $theDate = Carbon::create($year, $month, );
            } catch (\Exception $e) {
                return response()->json(['message' => 'Please provide a valid date.'], 400);
            }

            $student = $user->Student()->where('name', 'like', "%$studentName%")->first();

            if (!$student) {
                return response()->json(['message' => 'No student record found for the provided name.'], 404);
            }


            $attendance = $student->attendance()
                ->whereMonth('the_date', '=', $theDate->month)
                ->whereYear('the_date', '=', $theDate->year)
                ->get();

            if ($attendance->isEmpty()) {
                return response()->json(['message' => 'No attendance records found for the provided student and date.'], 404);
            }

            $attendanceHistory = $attendance->map(function ($record) {
                return [
                    'the_date' => Carbon::parse($record->the_date)->format('Y-m-d'),
                    'status' => $record->status,
                ];
            });


            $studentData = [
                'name' => $student->name,
                'category_name' => $student->category->name,
                'images' => $student->image_c()->get()->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'name' => $image->name,
                        'path' => $image->path,
                    ];
                }),
            ];

            return response()->json([
                'student' => $studentData,
                'student_attendance_history' => $attendanceHistory
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching the student attendance history.', 'error' => $e->getMessage()], 500);
        }
    }



    ////////////////////////////////المعلمات////////////////////////////////
    public function recordAttendance(Request $request)
    {
        $userId = Auth::id();

        $attendance = new AttendanceT();
        $attendance->user_id = $userId;
        $attendance->the_date = now();
        $attendance->present = 1; // Set present to 1 by default
        $attendance->save();

        return response()->json([
            'message' => 'Attendance recorded successfully.'
        ], 200);
    }
    ///////////////////////////////


    public function getAllAttendanceForTeacherByMonth(Request $request)
    {
        if (Auth::user()->role_id != 1 && Auth::user()->role_id != 2) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $userId = $request->input('user_id');
        $month = $request->input('month');
        $year = $request->input('year');

        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $attendances = AttendanceT::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role_id', 3);
            })
            ->whereBetween('the_date', [$startDate, $endDate])
            ->when($userId, function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        $attendancesData = $attendances->map(function ($attendance) {
            $createdAtString = $attendance->created_at?->format('H:i:s') ?? 'N/A';
            return [
                'id' => $attendance->id,
                'the_date' => $attendance->the_date,
                'present' => $attendance->present,
                'hour' => $createdAtString
            ];
        });

        if ($attendancesData->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'No attendance records found for the selected month.'], 200);
        }

        return response()->json(['success' => true, 'attendances' => $attendancesData], 200);
    }

    public function getAllAttendanceForTeacherByDate(Request $request, $user_id)
    {
        if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2) {
            $theDate = now()->format('Y-m-d');

            $attendances = AttendanceT::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('role_id', 3);
                })
                ->whereDate('the_date', $theDate)
                ->where('user_id', $user_id)
                ->get();

            if ($attendances->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No attendance records found for the specified user and date.'], 404);
            }

            $attendanceData = $attendances->map(function ($attendance) {
                return [
                    'attendance_id' => $attendance->id,
                    'the_date' => $attendance->the_date,
                    'present' => $attendance->present,
                    'hour' => $attendance->created_at->format('H:i:s')
                ];
            });

            return response()->json(['success' => true, 'attendances' => $attendanceData], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }
    }



}

