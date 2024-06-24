<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{

    ////////////////////اضافة الحجز من قبل الأهل ///////////////////
    public function add(Request $request)
    {
        if (Auth::user()->role_id != 4) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reservation = new Reservation();
        $reservation->appointment_id = $request->input('appointment_id');
        $reservation->user_id = Auth::id();
        $reservation->description = $request->input('description');
        $reservation->status = 'Not Accept';
        $reservation->save();

        return response()->json(['message' => 'Reservation added successfully'], 200);
    }

    ////////////عرض الطلبات المحجوزة للمديرة والمساعدة/////////////////

    public function view()
    {
        if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2) {
            $reservations = Reservation::where('reservations.status', 'Not Accept')
                ->with('user:id,first_name,last_name,image')
                ->leftJoin('appointments', 'reservations.appointment_id', '=', 'appointments.id')
                ->select('reservations.description', 'reservations.user_id', 'reservations.id', 'reservations.appointment_id', 'appointments.the_day', 'appointments.the_time')
                ->get();

            return response()->json($reservations);
        }
    }

    public function viewaccept()
    {
        if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2) {
            $reservations = Reservation::where('reservations.status','accept')
                ->with('user:id,first_name,last_name,image')
                ->leftJoin('appointments', 'reservations.appointment_id', '=', 'appointments.id')
                ->select('reservations.description', 'reservations.user_id', 'reservations.appointment_id', 'reservations.id','appointments.the_day', 'appointments.the_time')
                ->get();

            return response()->json($reservations);
        }
    }


    /////////////////تاكيد طلب الاهل من قبل الموديرة/////////////
    public function accept_reservation($id)

    {

        $userRole = Auth::user()->role_id;



        if ($userRole == 1 || $userRole == 2) {

            $reservation = Reservation::where('id', $id)->where('status', 'not accept')->first();



            if ($reservation) {

                $reservation->update(['status' => 'accept']);



                return response()->json([

                    'message' => 'Appointment booked successfully',

                    'reservation' => $reservation

                ]);

            } else {

                return response()->json([

                    'message' => 'The requested reservation does not exist or has already been accepted'

                ]);

            }

        } else {

            return response()->json([

                'message' => 'you are not authorized to do this'

            ], 403);

        }

    }

    //////////////////عرض طلب الاهل من الموافق عليه/////////////

    public function show()
    {

        if (Auth::user()->role_id == 4 ) {

            $reservations = Reservation::where('reservations.status', 'accept')
                ->with('user:id,first_name,last_name,image')
                ->leftJoin('appointments', 'reservations.appointment_id', '=', 'appointments.id')
                ->select('reservations.description', 'reservations.user_id', 'reservations.id', 'reservations.appointment_id', 'appointments.the_day', 'appointments.the_time')
                ->get();

            return response()->json($reservations);
        }
        else {

            return response()->json([

                'message' => 'you are not authorized to do this'

            ], 403);

        }

    }

    public function delete_record($id)
    {
        if (Auth::user()->role_id == 1 ||  Auth::user()->role_id == 2) {
        $record = Reservation::find($id);

        if (!$record) {
            return response()->json([
                'status' => false,
                'msg' => 'Invoice not found'
            ]);
        }

        $apponitment = $record->appointment();
        $apponitment->delete();

        $record->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Deleted successfully'
        ]);
    }
    else{

        return response()->json([
            'status' => true,
            'msg' => 'you are not authorized to do this'
        ]);
    }
    }

}

