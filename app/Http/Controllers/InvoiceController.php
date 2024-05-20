<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InvoiceController extends Controller
{
    public function createInvoice(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $studentId = $request->input('student_id');
        $batch = $request->input('batch');


        $student = Student::find($studentId);

        if (!$student) {
            return response()->json([
                'status' => false,
                'msg' => 'Student not found'
            ]);
        }

        $invoice = Invoice::create([
            'student_id' => $studentId,
            'batch' => $batch,

        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Invoice created successfully',
            'invoice'=>$invoice
        ]);
    }

    public function updateInvoice(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $invoiceId = $request->input('invoice_id');
        $studentId = $request->input('student_id');

        $invoice = Invoice::where('id', $invoiceId)
            ->where('student_id', $studentId)
            ->first();

        if (!$invoice) {
            return response()->json([
                'status' => false,
                'msg' => 'Invoice not found'
            ]);
        }
        $invoice->batch = $request->filled('batch') ? $request->input('batch') : $invoice->batch;

        $invoice->save();

        return response()->json([
            'status' => true,
            'msg' => 'Invoice updated successfully'
        ]);
    }

    public function deleteInvoice(Request $request,$invoiceId)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return response()->json([
                'status' => false,
                'msg' => 'Invoice not found'
            ]);
        }

        $invoice->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Invoice deleted successfully'
        ]);
    }

    public function getStudentInvoicesTotal($studentId)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $invoices = Invoice::where('student_id', $studentId)->get();

        if ($invoices->isEmpty()) {
            return response()->json([
                'status' => false,
                'msg' => 'No invoices found for the student'
            ]);
        }

        $total = $invoices->sum('batch');

        return response()->json([
            'status' => true,
            'total' => $total
        ]);
    }

}
