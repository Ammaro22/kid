<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    public function signup(Request $request)
    {
        $validator = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|min:4|max:15',
            'phone' => 'required|string|max:10|min:10',
            'age' => 'nullable|string',
            'image' => 'image',
            'certificate' => 'nullable|string',
            'Previous_places_work' => 'nullable|string',
            'Experience' => 'nullable|string',
            'salary' => 'nullable|string',
            'role_id'
        ]);

        $validator['password'] = bcrypt($request->password);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $newImage = time() . $image->getClientOriginalName();
            $image->move(public_path('upload'), $newImage);
            $path = "upload/$newImage";
        } else {
            $path = null;
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password' => $validator['password'],
            'age' => $request->age,
            'phone' => $request->phone,
            'image' => $path,
            'certificate' => $request->certificate,
            'Previous_places_work' => $request->certificate,
            'Experience' => $request->certificate,
            'salary' => $request->certificate,
            'role_id' => $request->role_id
        ]);

        $accessToken = $user->createToken('authToken')->accessToken;
        return response()->json(['user' => $user, 'access_token' => $accessToken]);
    }

    public function login(Request $request)
    {
        $data = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required'
        ]);

        if ($data->fails()) {
            return response(['errors' => $data->errors()->all()], 422);
        }

        $credentials = request(['phone', 'password']);

        if (!auth()->attempt($credentials)) {
            return response(['errors' => 'Incorrect Details. Please try again'], 422);
        }

        $user = $request->user();
        $token =  $user->createToken('Personal Access Token')->accessToken;

        return response(['user' => $user, 'token' => $token]);
    }
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);

    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = Validator::make($request->all(), [
            'password' => 'string|min:4',

        ]);

        if ($data->fails()) {
            return response()->json(['error' => $data->errors()], 400);
        }

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $newImage = time().$image->getClientOriginalName();
            $image->move(public_path('upload'), $newImage);
            $path = "upload/$newImage";

            if ($user->image) {
                $imagePath = public_path($user->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $user->image = $path;
        }

        if ($request->filled('delete_image')) {

            if ($user->image) {
                $imagePath = public_path($user->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $user->image = null;
        }
        $user->first_name = $request->filled('first_name') ? $request->input('first_name') : $user->first_name;
        $user->last_name = $request->filled('last_name') ? $request->input('last_name') : $user->last_name;
        $user->password = $request->filled('password') ? bcrypt($request->input('password')) : $user->password;
        $user->age = $request->filled('age') ? $request->input('age') : $user->age;
        $user->phone = $request->filled('phone') ? $request->input('phone') : $user->phone;
        $user->certificate = $request->filled('certificate') ? $request->input('certificate') : $user->certificate;
        $user->Previous_places_work = $request->filled('Previous_places_work') ? $request->input('Previous_places_work') : $user->Previous_places_work;
        $user->Experience = $request->filled('Experience') ? $request->input('Experience') : $user->Experience;
        $user->salary = $request->filled('salary') ? $request->input('salary') : $user->salary;
        $user->role_id = $request->filled('role_id') ? $request->input('role_id') : $user->role_id;

        $user->save();

        return response()->json([
            'message' => 'user update successfully.',
            'user' => $user
        ]);
    }

    public function updateteacher(Request $request , $teacherid)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = User::find($teacherid);

        $data = Validator::make($request->all(), [
            'password' => 'string|min:4',

        ]);

        if ($data->fails()) {
            return response()->json(['error' => $data->errors()], 400);
        }

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $newImage = time().$image->getClientOriginalName();
            $image->move(public_path('upload'), $newImage);
            $path = "upload/$newImage";

            if ($user->image) {
                $imagePath = public_path($user->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $user->image = $path;
        }

        if ($request->filled('delete_image')) {

            if ($user->image) {
                $imagePath = public_path($user->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $user->image = null;
        }
        $user->first_name = $request->filled('first_name') ? $request->input('first_name') : $user->first_name;
        $user->last_name = $request->filled('last_name') ? $request->input('last_name') : $user->last_name;
        $user->password = $request->filled('password') ? bcrypt($request->input('password')) : $user->password;
        $user->age = $request->filled('age') ? $request->input('age') : $user->age;
        $user->phone = $request->filled('phone') ? $request->input('phone') : $user->phone;
        $user->certificate = $request->filled('certificate') ? $request->input('certificate') : $user->certificate;
        $user->Previous_places_work = $request->filled('Previous_places_work') ? $request->input('Previous_places_work') : $user->Previous_places_work;
        $user->Experience = $request->filled('Experience') ? $request->input('Experience') : $user->Experience;
        $user->salary = $request->filled('salary') ? $request->input('salary') : $user->salary;
        $user->role_id = $request->filled('role_id') ? $request->input('role_id') : $user->role_id;

        $user->save();

        return response()->json([
            'message' => 'user update successfully.',
            'user' => $user
        ]);
    }

    public function destroy($id)
    {
        $us = User::find($id);
        $us->delete();
        return Response()->json(['message' => 'user deleted successfully.']);
    }
    public  function profile(){
        $user_data = auth()->user();
        return response()->json([
            "message"=> "User data",
            "data"=>$user_data
        ]);

    }


    public function getallteacher()
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $users = User::where('role_id', 3)->select('id','first_name','last_name', 'image','age','certificate','Previous_places_work','Experience','salary')->get();

        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    public function getTeacherById($id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('id', $id)->where('role_id', 3)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found with the given ID and role',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $data = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string|min:4',
        ]);

        if ($data->fails()) {
            return response()->json(['error' => $data->errors()], 400);
        }

        $user = User::where('phone', $request->input('phone'))->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }


        $user->password = bcrypt($request->input('password'));
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',

        ]);
    }


}
