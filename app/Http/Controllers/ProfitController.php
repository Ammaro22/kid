<?php

namespace App\Http\Controllers;

use App\Models\Disbursed_invoice;
use App\Models\Invoice;
use App\Models\Profit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProfitController extends Controller
{
    public function calculateProfit()
    {

        $totalInvoices = Invoice::sum('batch');

        $totalDisbursedInvoices = Disbursed_invoice::sum('price');

        $profitAmount = $totalInvoices - $totalDisbursedInvoices;


        $profit = Profit::create(['profits' => $profitAmount]);


        return response()->json([ 'profit' => $profit], 200);
    }

    public function showProfit(Request $request)
    {
        $day = $request->input('day');
        $month = $request->input('month');
        $year = $request->input('year');


        $profits = Profit::whereDay('created_at', $day)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();


        return response()->json(['profit' =>$profits], 200);
    }



}
