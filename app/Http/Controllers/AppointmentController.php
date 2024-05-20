<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
/////////////////////////إضافة المواعيد المتاحة///////////////////////
    public function add(Request $request)
    {
        if(Auth::check()){
        if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2) {
            $appointment = new Appointment();
            $appointment->user_id = Auth::id();
            $appointment->the_day = $request->input('the_day');
            $appointment->the_time = $request->input('the_time');
            $appointment->status = 'available';

            $result = $appointment->save();
            if ($result) {
                return response()->json(['message' => 'add Successfully'], 200);
            } else {
                return response()->json(['message' => 'Error'], 404);
            }
        }}
        return response()->json(['message' => 'you are not authorized to do this'], 403);
    }

    ///////////عرض المواعيد المتاحة/////////
    public function showAvailableAppointments()
    {
        $availableAppointments = Appointment::where('status', 'available')->get();
        return response()->json([
            'success' => true,
            'data' => $availableAppointments
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2) {
            $appointment = Appointment::find($id);

                if ($request->has('the_day')) {
                    $appointment->the_day = $request->input('the_day');
                }

                if ($request->has('the_time')) {
                    $appointment->the_time = $request->input('the_time');
                }

                $result = $appointment->save();

                if ($result) {
                    return response()->json([
                        'message' => 'Update Successfully',
                        'data' => [

                            'after_update' => $appointment,
                        ]
                    ], 200);
                } else {
                    return response()->json(['message' => 'Error updating appointment'], 500);
                }
            } else {
                return response()->json(['message' => 'Appointment not found'], 404);
            }
        }



    public function deleteAvailableAppointment($id)
    {
        $userRole = Auth::user()->role_id;
        if ($userRole == 1 || $userRole == 2) {
            $appointment = Appointment::where('status', 'available')->find($id);

            // Check if the appointment is found before attempting to delete it
            if ($appointment) {
                $result = $appointment->delete();

                if ($result) {
                    return response()->json(['message' => 'Appointment Deleted Successfully',], 200);
                }
            } else {
                return response()->json(['error' => 'Appointment not found'], 404);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
