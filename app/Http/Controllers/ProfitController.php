<?php

namespace App\Http\Controllers;

use App\Models\Disbursed_invoice;
use App\Models\Invoice;
use App\Models\Profit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProfitController extends Controller
{
//    public function calculateProfit()
//    {
//        $userRole = auth()->user()->role_id;
//        if ($userRole !== 1 ) {
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//        $totalInvoices = Invoice::sum('batch');
//
//        $totalDisbursedInvoices = Disbursed_invoice::sum('price');
//
//        $profitAmount = $totalInvoices - $totalDisbursedInvoices;
//
//
//        $profit = Profit::create(['profits' => $profitAmount]);
//
//
//        return response()->json([ 'profit' => $profit], 200);
//    }
//
//    public function showProfit(Request $request)
//    {
//        $userRole = auth()->user()->role_id;
//        if ($userRole !== 1) {
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//        $day = $request->input('day');
//        $month = $request->input('month');
//        $year = $request->input('year');
//
//
//        $profits = Profit::whereDay('created_at', $day)
//            ->whereMonth('created_at', $month)
//            ->whereYear('created_at', $year)
//            ->get();
//
//        if (!$profits)
//        {
//            return response()->json(['message'=>'not found'], 404);
//        }
//        else{
//        return response()->json(['profit' =>$profits], 200);}
//    }



}
