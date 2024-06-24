<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClassSupervisorController;
use App\Http\Controllers\DisbursedInvoiceController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PermanentProgramController;
use App\Http\Controllers\ProfitController;
use App\Http\Controllers\RecordOrderController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
/*المستخدم*/
Route::post('signup',[UserController::class,'signup']);
Route::post('login', [UserController::class, 'login']);
Route::delete('user_delete/{id}',[UserController::class,'destroy']);
Route::group(["middleware"=>["auth:api"]],function (){
    Route::get('profile',[UserController::class,'profile']);
    Route::post('logout',[UserController::class,'logout']);
    Route::post('user_update',[UserController::class,'update']);

});
/*عرض كل المعلمات*/
Route::post('admin_update_techer/{id}',[UserController::class,'updateteacher'])->middleware('auth:api');
Route::post('show_all_techer',[UserController::class,'getallteacher'])->middleware('auth:api');
Route::post('show_techer/{id}',[UserController::class,'getteacherbyid'])->middleware('auth:api');
/*تحديد مشرف الصف*/
Route::post('select_name_teacher',[ClassSupervisorController::class,'storeClassSupervisor'])->middleware('auth:api');
Route::post('update_name_teacher/{id}',[ClassSupervisorController::class,'updateClassSupervisor'])->middleware('auth:api');
Route::post('show_supervisor',[ClassSupervisorController::class,'getClassSupervisor']);
Route::delete('delete_class_supervisor/{id}',[ClassSupervisorController::class,'deleteClassSupervisor']);
/*الطالب*/
Route::post('student_registration',[StudentController::class,'store'])->middleware('auth:api');
Route::post('update_info_student/{id}',[StudentController::class,'update'])->middleware('auth:api');
Route::post('show_student/{id}',[StudentController::class,'showStudent']);
Route::delete('delete_student/{id}',[StudentController::class,'destroy'])->middleware('auth:api');
/*عرض معلومات الطفل لاهله*/
Route::post('show_student_for_parent',[StudentController::class,'showStudentforparent'])->middleware('auth:api');
/*تقرير الطلب*/
Route::post('Record_student',[RecordOrderController::class,'Record'])->middleware('auth:api');
Route::post('accept_record/{id}', [RecordOrderController::class, 'acceptrecord'])->middleware('auth:api');
Route::delete('inaccept_record/{id}', [RecordOrderController::class, 'delete_record'])->middleware('auth:api');
/*البحث عن طالب*/
Route::post('search_student',[StudentController::class,'searchStudents']);
/*الفاتورة*/

Route::post('create_invoice',[InvoiceController::class,'createInvoice'])->middleware('auth:api');
Route::post('update_invoice',[InvoiceController::class,'updateInvoice'])->middleware('auth:api');
Route::delete('delete_invoice/{id}',[InvoiceController::class,'deleteInvoice'])->middleware('auth:api');
Route::post('get&&sum_invoices/{id}', [InvoiceController::class, 'getStudentInvoicesTotal'])->middleware('auth:api');
Route::post('get_invoices_by_category/{id}', [InvoiceController::class, 'getStudentInvoicesByCategoryTotal'])->middleware('auth:api');
Route::get('get_total_invoices_for_category', [InvoiceController::class, 'getTotalInvoicesByCategory'])->middleware('auth:api');

/*فواتير الابناء بالنسة للاهل*/
Route::get('get_invoices_for_parent',[InvoiceController::class,'getInvoicesByStudent'])->middleware('auth:api');
/*الاحداث*/

Route::post('create_activity',[ActivityController::class,'create'])->middleware('auth:api');
Route::post('update_activity/{id}',[ActivityController::class,'update'])->middleware('auth:api');
Route::post('show_activity/{id}',[ActivityController::class,'show']);
Route::get('show_all_activity',[ActivityController::class,'showallactivity']);
Route::delete('delete_activity/{id}',[ActivityController::class,'destroy'])->middleware('auth:api');


/*برنامج الدوام*/
Route::post('create_week/{id}',[PermanentProgramController::class,'createProgram'])->middleware('auth:api');
Route::post('update_week/{id}',[PermanentProgramController::class,'updateProgramSubjects'])->middleware('auth:api');
Route::post('show_week/{id}',[PermanentProgramController::class,'showProgram']);
Route::delete('delete_week/{id}',[PermanentProgramController::class,'deleteProgram'])->middleware('auth:api');

///////////////////appointments/////////////////////////////////

Route::post('add_appointment',[AppointmentController::class,'add'])->middleware('auth:api');
Route::get('show_appointments/available', [AppointmentController::class, 'showAvailableAppointments']);
Route::post('update_appointments/{id}', [AppointmentController::class,'update'])->middleware('auth:api');
Route::delete('delete_appointments/{id}', [AppointmentController::class,'deleteAvailableAppointment'])->middleware('auth:api');
/////////////reservation/////////////

Route::post('add_reservation',[ReservationController::class,'add'])->middleware('auth:api');
/*عرض الحجوزات المقبولة وغير المقبولة للادمن والمساعدة*/
Route::get('view_reservation_for_admin', [ReservationController::class, 'view'])->middleware('auth:api');
/*عرض الحجوزات المقبولة للادمن والمساعدة*/
Route::get('view_accept_reservation_for_admin', [ReservationController::class, 'viewaccept'])->middleware('auth:api');
/* تاكيد الحجوزات للاهل*/
Route::post('accept_reservation_by_admin/{id}', [ReservationController::class, 'accept_reservation'])->middleware('auth:api');
/*عرض الحجوزات للاهل*/
Route::get('view_reservation_for_parent', [ReservationController::class, 'show'])->middleware('auth:api');
/* رفض الحجوزات للاهل*/
Route::delete('inaccept_reservation_by_admin/{id}', [ReservationController::class, 'delete_record'])->middleware('auth:api');

/*التقيمات*/
Route::post('add_evaluation',[EvaluationController::class,'createEvaluation'])->middleware('auth:api');
Route::post('update_evaluation/{id}',[EvaluationController::class,'updateEvaluation'])->middleware('auth:api');
Route::delete('delete_evaluation/{id}',[EvaluationController::class,'deleteEvaluation'])->middleware('auth:api');
Route::post('show__evaluationstudent_by_day/{id}',[EvaluationController::class,'showEvaluations']);
Route::post('show__evaluationstudent_by_month/{id}',[EvaluationController::class,'showEvaluationsmonth']);
Route::post('show__evaluationstudent_by_category/{id}',[EvaluationController::class,'getEvaluationsByCategoryId']);
/*عرض تقييمات الطفل بالنسبة للاهل*/
Route::post('show__evaluationstudent_by_day_for_parent',[EvaluationController::class,'showEvaluationsforparent'])->middleware('auth:api');
Route::post('show__evaluationstudent_by_month_for_parent',[EvaluationController::class,'showEvaluationsforparentmonth'])->middleware('auth:api');

/*أضافة او تعديل الملاحظة التي تضيفها المديرة للطالب*/
Route::post('update_note_sudent/{id}',[NoteController::class,'updateNoteAdmin'])->middleware('auth:api');


//////////////////حضور الطلاب/////////////////
Route::post('add_attendance',[AttendanceController::class,'add'])->middleware('auth:api');
Route::get('students_attendance_status/{id}',[AttendanceController::class,'checkStudentAttendanceStatus'])->middleware('auth:api');
/*عرض حضور الطالب لاهله*/
Route::get('students_attendance_status_for_parent',[AttendanceController::class,'checkStudentAttendanceStatusforparent'])->middleware('auth:api');
//////////////////حضور المعلمات/////////////////
Route::get('make-attendance',[AttendanceController::class,'makeAttendance'])->middleware('auth:api')->name('mark-attendance');
Route::get('generate_qr_code', [AttendanceController::class, 'generateQrCode'])->middleware('auth:api');

/*السجلات المالية*/
Route::post('disbursed_invoices',[DisbursedInvoiceController::class,'createDisbursedInvoice'])->middleware('auth:api');
Route::post('update_disbursed_invoices/{id}',[DisbursedInvoiceController::class,'updateDisbursedInvoice'])->middleware('auth:api');
Route::get('show_disbursed_invoices_by_invoice_type/{id}',[DisbursedInvoiceController::class,'getDisbursedInvoicesByType'])->middleware('auth:api');
Route::get('show_all_disbursed_invoices',[DisbursedInvoiceController::class,'getAllDisbursedInvoices'])->middleware('auth:api');
Route::get('get_total__price_disbursed_invoices_by_invoice_type/{id}',[DisbursedInvoiceController::class,'getTotalPriceByInvoiceType'])->middleware('auth:api');
Route::get('get_total__price_disbursed_invoices',[DisbursedInvoiceController::class,'getTotalPrice'])->middleware('auth:api');
Route::delete('delete_disbursed_invoices/{id}',[DisbursedInvoiceController::class,'deleteDisbursedInvoice'])->middleware('auth:api');
/*الربح*/
Route::get('calculate_Profit',[ProfitController::class,'calculateProfit'])->middleware('auth:api');
Route::get('show_Profit_by_day',[ProfitController::class,'showProfit'])->middleware('auth:api');
