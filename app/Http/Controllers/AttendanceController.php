<?php

namespace App\Http\Controllers;
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
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    ////////////اضافة حالة حضور الطالب//////////////////////
    public function add(Request $request)
    {
        if (Auth::check()) {
            if (Auth::user()->role_id == 3) {
                $attendance = new Attendance ();
                $attendance->user_id = Auth::id();
                $attendance->student_id = $request->input('student_id');
                $attendance->the_date = $request->input('the_date');
                $attendance->status = $request->input('status');
                $result = $attendance->save();
                if ($result) {
                    return response()->json(['message' => 'add Successfully'], 200);
                } else {
                    return response()->json(['message' => 'Error'], 404);
                }
            }
        }
        return response()->json(['message' => 'you are not authorized to do this'], 403);
    }

    //////////////////عرض حالة حضور الطالب من قبل المديرة و الأهل ///////////////
    public function checkStudentAttendanceStatus($id)
    {
        if (Auth::check()) {
            if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2   ) {
                $attendance = Attendance::where('student_id', $id)
                    ->whereDate('the_date', Carbon::today())
                    ->first();
                if ($attendance) {
                    return response()->json(['message' => 'Successfully retrieved attendance status', 'status' => $attendance->status], 200);
                } else {
                    return response()->json(['message' => 'No attendance record found for the student on the current day'], 404);
                }
            }
        }
        return response()->json(['message' => 'You are not authorized to perform this action'], 403);
    }

    /* عرض حضور الطالب بالنسبة لاهله*/
    public function checkStudentAttendanceStatusforparent()
    {
        if (Auth::check()) {
            if (Auth::user()->role_id == 4) {
        $user = auth()->user();
        $student = $user->Student;

        if ($student) {
            $attendance = Attendance::where('student_id', $student->id)
                ->whereDate('the_date', Carbon::today())
                ->first();

            if ($attendance) {
                return response()->json(['message' => 'Successfully retrieved attendance status', 'status' => $attendance->status], 200);
            } else {
                return response()->json(['message' => 'No attendance record found for the student on the current day'], 404);
            }
        } else {
            return response()->json(['message' => 'The authenticated user is not associated with a student'], 404);
        }
    }}}



    /////////////////////////////////////////////////////////
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

    //////////////////////
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

