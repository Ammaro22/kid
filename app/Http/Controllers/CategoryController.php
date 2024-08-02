<?php

namespace App\Http\Controllers;

use App\Models\AttendanceT;
use App\Models\Category;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function create(Request $request){

    $ss = Category::create([
        'name'=>$request->name,
    ]);
    return response()->json(['ss'=>$ss]);
}



    public function readQrCode(Request $request)
    {
        if (Auth::user()->role_id != 3) {
            return response()->json(['message' => 'You are not authorized to do this'], 403);
        }
        $the_date = $request->input('the_date');

        AttendanceT::create([
            'user_id' => Auth::id(),
            'the_date' => $the_date,
            'present' => true,
        ]);

        return response()->json([
            'message' => 'Attendance recorded successfully.',
        ], 200);
    }



//    public function readQrCode(Request $request)
//    {
//        $userRole = auth()->user()->role_id;
//        if ($userRole !== 1 && $userRole !== 3) {
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//
//        $qrCodePath = $request->input('qr_code_path');
//        if (!$qrCodePath) {
//            return response()->json(['message' => 'QR code path is required'], 400);
//        }
//
//        $qrCodeDate = $this->getQrCodeDate($qrCodePath);
//        if (!$qrCodeDate) {
//            return response()->json(['message' => 'Invalid QR code'], 400);
//        }
//
//        $attendanceDate = now()->format('Y-m-d');
//        if ($qrCodeDate !== $attendanceDate) {
//            return response()->json(['message' => 'QR code expired'], 400);
//        }
//
//        $attendance = Attendance::where('user_id', auth()->id())
//            ->where('date', $attendanceDate)
//            ->first();
//        if ($attendance) {
//            return response()->json(['message' => 'Attendance already recorded'], 400);
//        }
//
//        // Record attendance
//        Attendance::create([
//            'user_id' => auth()->id(),
//            'date' => $attendanceDate,
//        ]);
//
//        return response()->json(['message' => 'Attendance recorded'], 200);
//    }
//
//    private function getQrCodeDate($qrCodePath)
//    {
//        $fileName = basename($qrCodePath);
//        $datePrefix = 'qr_code_';
//        $dateLength = 10;
//
//        if (stripos($fileName, $datePrefix) === 0) {
//            $dateString = substr($fileName, strlen($datePrefix), $dateLength);
//            try {
//                return Carbon::createFromFormat('Ymd', $dateString)->toDateString();
//            } catch (\Exception $e) {
//                // If the date string is not in the expected format, return null
//                return null;
//            }
//        }
//
//        return null;
//    }

    ///////////////////////////////

//    public function generateQrCode()
//    {
//        $userRole = auth()->user()->role_id;
//        if ($userRole !== 1 && $userRole !== 2) {
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//        $the_date = now()->format('Y-m-d');
//        $options = new QROptions();
//
//        $options->version             = 9;
//        $options->scale               = 6;
//        $options->outputBase64        = false;
//        $options->bgColor             = [200, 150, 200];
//        $options->imageTransparent    = true;
//        $options->keepAsSquare        = [
//            QRMatrix::M_FINDER_DARK,
//            QRMatrix::M_FINDER_DOT,
//        ];
//
//        $options->addQuietzone    = true;
//
//        $qrcode = (new QRCode($options));
//        $qrcode->addByteSegment($the_date);
//        $qrOutputInterface = (new QRGdImagePNG($options, $qrcode->getQRMatrix()))->dump();
//
//        $disk = 'public';
//        $filePath = 'qr/' . time() . '.png';
//
//        Storage::disk($disk)->put($filePath, $qrOutputInterface);
//
//        return response()->json([
//            'qr_code_path' => Storage::disk($disk)->url($filePath),
//        ], 200);
//    }

    public function generateQrCode()
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $the_date = now()->format('Y-m-d');
        $filePath = 'qr/qr_code.png';


        if (Storage::disk('public')->exists($filePath)) {

            $this->updateQrCodeDate($filePath, $the_date);
        } else {

            $this->generateAndStoreQrCode($the_date, $filePath);
        }

        $publicUrl = Storage::disk('public')->url($filePath);
        return response()->json(['qr_code_path' => $publicUrl], 200);
    }

    private function updateQrCodeDate($filePath, $the_date)
    {
        $options = new QROptions();
        $options->version = 9;
        $options->scale = 6;
        $options->outputBase64 = false;
        $options->bgColor = [200, 150, 200];
        $options->imageTransparent = true;
        $options->keepAsSquare = [
            QRMatrix::M_FINDER_DARK,
            QRMatrix::M_FINDER_DOT,
        ];
        $options->addQuietzone = true;

        $qrcode = (new QRCode($options));
        $qrcode->addByteSegment($the_date);
        $qrOutputInterface = (new QRGdImagePNG($options, $qrcode->getQRMatrix()))->dump();

        Storage::disk('public')->put($filePath, $qrOutputInterface);
    }

    private function generateAndStoreQrCode($the_date, $filePath)
    {
        $options = new QROptions();
        $options->version = 9;
        $options->scale = 6;
        $options->outputBase64 = false;
        $options->bgColor = [200, 150, 200];
        $options->imageTransparent = true;
        $options->keepAsSquare = [
            QRMatrix::M_FINDER_DARK,
            QRMatrix::M_FINDER_DOT,
        ];
        $options->addQuietzone = true;

        $qrcode = (new QRCode($options));
        $qrcode->addByteSegment($the_date);
        $qrOutputInterface = (new QRGdImagePNG($options, $qrcode->getQRMatrix()))->dump();

        Storage::disk('public')->put($filePath, $qrOutputInterface);
    }


    //////////////ارجاع صورة QR///////////
    public function getQrImage(Request $request)
    {
        $qrImageDirectory = 'public/qr/';
        $files = Storage::files($qrImageDirectory);

        if (count($files) > 0) {

            usort($files, function($a, $b) {
                return filemtime(Storage::path($b)) - filemtime(Storage::path($a));
            });
            $newestFile = $files[0];
            $imageName = basename($newestFile);
            $imagePath = $qrImageDirectory . $imageName;

            return response()->file(Storage::path($imagePath));
        } else {
            return response()->json([
                'message' => "No QR code images found"
            ], 404);
        }
    }


}
