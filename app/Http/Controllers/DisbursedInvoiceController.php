<?php

namespace App\Http\Controllers;

use App\Models\Disbursed_invoice;
use App\Models\Invoice;
use App\Models\invoice_type;
use App\Models\Profit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DisbursedInvoiceController extends Controller
{
    public function createDisbursedInvoice(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validatedData = $request->validate([
            'price' => 'required|numeric',
            'invoice_type_id' => 'required',
            'description' => 'nullable|string',
        ]);

        $invoiceType = invoice_type::find($validatedData['invoice_type_id']);
        if (!$invoiceType) {
            return response()->json([
                'error' => 'Invoice type not found',
            ], 404);
        }


        $disbursedInvoiceData = [
            'price' => $validatedData['price'],
            'invoice_type_id' => $validatedData['invoice_type_id'],
            'description' => $validatedData['description'] ?? null,
        ];

        $disbursedInvoice = Disbursed_invoice::create($disbursedInvoiceData);

        return response()->json([
            'message' => 'Disbursed invoice created successfully',
            'invoice' => $disbursedInvoice,
        ], 201);
    }

    public function updateDisbursedInvoice(Request $request, $id)
    {

        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
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
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $year = $request->input('year');

        $disbursedInvoices = Disbursed_invoice::where('invoice_type_id', $invoiceTypeId)
            ->whereYear('created_at', $year)
            ->with('invoice_ty')
            ->get()
            ->groupBy(function ($item) use ($year, $invoiceTypeId) {
                return "$year-$invoiceTypeId";
            })
            ->map(function ($group) use ($invoiceTypeId) {
                return [
                    'year' => $group->first()->created_at->format('Y'),
                    'invoice_type_id' => $invoiceTypeId,
                    'invoices' => $group->map(function ($invoice) {
                        return [
                            'id' => $invoice->id,
                            'invoice_type' => $invoice->invoice_ty->name,
                            'price' => $invoice->price,
                            'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();

        if (empty($disbursedInvoices)) {
            return response()->json(['message' => 'No invoices found for the given criteria'], 404);
        }

        return response()->json([
            'invoices' => $disbursedInvoices
        ], 200);
    }

    public function getAllDisbursedInvoices(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $year = $request->input('year');

        $disbursedInvoices = Disbursed_invoice::with('invoice_ty')
            ->whereYear('created_at', $year)
            ->get();

        if ($disbursedInvoices->isEmpty()) {
            return response()->json([
                'message' => "No disbursed invoices found for the year $year.",
            ], 200);
        }

        $invoices = $disbursedInvoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'price' => $invoice->price,
                'invoice_type_id' => $invoice->invoice_type_id,
                'invoice_type_name' => $invoice->invoice_ty->name,
                'created_at' => $invoice->created_at->format('Y-m-d'),
            ];
        });

        return response()->json([
            'invoices' => $invoices,
        ], 200);
    }



    public function getTotalPriceandProfitByyear(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $year = $request->input('year', date('Y'));

        $invoiceTypes = [
            1 => 'Occasions',
            2 => 'School Uniforms',
            3 => 'Stationary',
            4 => 'Other',
        ];

        $totalPrices = [];
        $grandTotal = 0;
        foreach ($invoiceTypes as $invoiceTypeId => $invoiceTypeName) {
            $total = Disbursed_invoice::whereYear('created_at', $year)
                ->where('invoice_type_id', $invoiceTypeId)
                ->sum('price');
            $totalPrices[$invoiceTypeName] = $total;
            $grandTotal += $total;
        }

        $totalInvoices = Invoice::whereYear('created_at', $year)->sum('batch');
        $profitAmount = $totalInvoices - $grandTotal;

        // Create the profit record
        $profit = Profit::create(['profits' => $profitAmount, 'year' => $year]);

        return response()->json([
            'total_prices' => $totalPrices,
            'grand_total' => $grandTotal,
            'profit' => $profitAmount,
            'year' => $year,
        ], 200);
    }


    public function deleteDisbursedInvoice($id)
    {

        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $invoice = Disbursed_invoice::findOrFail($id);
if (!$invoice)
{

    return response()->json([
        'error' => 'Disbursed invoice not found',
    ], 404);
}
        $invoice->delete();

        return response()->json([
            'message' => 'Disbursed invoice deleted successfully',
        ], 200);
    }



}
