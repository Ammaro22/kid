<?php

namespace App\Http\Controllers;

use App\Models\Disbursed_invoice;
use App\Models\invoice_type;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DisbursedInvoiceController extends Controller
{
    public function createDisbursedInvoice(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validatedData = $request->validate([
            'price' => 'required|numeric',
            'invoice_type_id' => 'required|exists:invoice_types,id',
        ]);
        $invoiceType = invoice_type::find($validatedData['invoice_type_id']);
        if (!$invoiceType) {
            return response()->json([
                'error' => 'invoice type not found ',
            ], 404);
        }

        $disbursedInvoice = Disbursed_invoice::create($validatedData);


        return response()->json([
            'message' => 'Disbursed invoice created successfully',
            'invoice' => $disbursedInvoice,
        ], 201);
    }

    public function updateDisbursedInvoice(Request $request, $id)
    {

        $userRole = auth()->user()->role_id;
        if ($userRole !== 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $disbursedInvoice = Disbursed_invoice::findOrFail($id);


        $validatedData = $request->validate([
            'price' => 'sometimes|required|numeric',
            'invoice_type_id' => 'sometimes|required|exists:invoice_types,id',
        ]);


        if ($request->has('price')) {
            $disbursedInvoice->price = $validatedData['price'];
        }


        if ($request->has('invoice_type_id')) {
            $invoiceType = invoice_type::find($validatedData['invoice_type_id']);
            if (!$invoiceType) {
                return response()->json([
                    'error' => 'invoice type not found ',
                ], 404);
            }
            $disbursedInvoice->invoice_type_id = $validatedData['invoice_type_id'];
        }

        $disbursedInvoice->update($validatedData);


        return response()->json([
            'message' => 'Disbursed invoice updated successfully',
            'invoice' => $disbursedInvoice,
        ], 200);
    }

    public function getDisbursedInvoicesByType(Request $request, $invoiceTypeId)
    {

        $userRole = auth()->user()->role_id;
        if ($userRole !== 1) {
            return response()->json(['message' => 'You are not authorized to perform this action'], 401);
        }


        $disbursedInvoices = Disbursed_invoice::where('invoice_type_id', $invoiceTypeId)
            ->with('invoice_ty')
            ->get();


        return response()->json([
            'invoices' => $disbursedInvoices,
        ], 200);
    }

    public function getAllDisbursedInvoices(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1) {
            return response()->json(['message' => 'You are not authorized to perform this action'], 401);
        }


        $disbursedInvoices = Disbursed_invoice::with('invoice_ty')->get();


        $invoices = $disbursedInvoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'price' => $invoice->price,
                'invoice_type_id' => $invoice->invoice_type_id,
                'invoice_type_name' => $invoice->invoice_ty->name,
            ];
        });


        return response()->json([
            'invoices' => $invoices,
        ], 200);
    }

//    public function getTotalPriceByInvoiceType($invoiceTypeId)
//    {
//        $userRole = auth()->user()->role_id;
//        if ($userRole !== 1) {
//            return response()->json(['message' => 'You are not authorized to perform this action'], 401);
//        }
//
//
//        $totalPrice = Disbursed_invoice::where('invoice_type_id', $invoiceTypeId)
//            ->sum('price');
//
//
//        return response()->json([
//            'total_price' => $totalPrice,
//        ], 200);
//    }

    public function getTotalPriceByInvoiceType()
    {
        $userRole = auth()->user()->role_id;
        if ($userRole != 1) {
            return response()->json(['message' => 'You are not authorized to perform this action'], 401);
        }

        $invoiceTypes = [
            1 => 'Occasions',
            2 => 'School Uniforms',
            3 => 'Stationary',
            4 => 'Other',
        ];

        $totalPrices = [];
        $grandTotal = 0;
        foreach ($invoiceTypes as $invoiceTypeId => $invoiceTypeName) {

            $total = Disbursed_invoice::where('invoice_type_id', $invoiceTypeId)->sum('price');
            $totalPrices[$invoiceTypeName] = $total;
            $grandTotal += $total;
        }

        return response()->json([
            'total_prices' => $totalPrices,
            'grand_total' => $grandTotal,
        ], 200);
    }

    public function getTotalPrice()
    {

        $userRole = auth()->user()->role_id;
        if ($userRole !== 1) {
            return response()->json(['message' => 'You are not authorized to perform this action'], 401);
        }

        $invoices = Disbursed_invoice::
            all();
        $totalPrice = $invoices->sum('price');
        return response()->json([
            'total_price' => $totalPrice,
        ], 200);
    }

    public function deleteDisbursedInvoice($id)
    {

        $userRole = auth()->user()->role_id;
        if ($userRole !== 1) {
            return response()->json(['message' => 'You are not authorized to perform this action'], 401);
        }


        $invoice = Disbursed_invoice::findOrFail($id);
if (!$invoice)
{

    return response()->json([
        'error' => 'Disbursed invoice not found',
    ], 200);
}
        $invoice->delete();

        return response()->json([
            'message' => 'Disbursed invoice deleted successfully',
        ], 200);
    }



}
