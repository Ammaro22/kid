<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\File;
use App\Models\Image;

use App\Traits\Imageable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    use Imageable;



    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|string|max:255',
            'post' => 'required|string|max:255',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'videos.*' => 'mimes:mp4,mov,avi',
            'pdf.*' => 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:2048'
        ]);



        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $activity = Activity::create([
            'name' => $request->name,
            'date' => $request->date,
            'post' => $request->post,
        ]);

        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $savedImages = Imageable::ssave($files, $activity->id);

            return response()->json(['message' => 'activity created successfully', 'item' => $activity], 200);
        }

        if ($request->hasFile('videos')) {
            $files = $request->file('videos');
            $savedfiles = Imageable::sssave($files, $activity->id);

            return response()->json(['message' => 'activity created successfully', 'item' => $activity], 200);
        }

        if ($request->hasFile('pdf')) {
            $files = $request->file('pdf');
            $savedfiles = Imageable::ssssave($files, $activity->id);

            return response()->json(['message' => 'Activity created successfully', 'item' => $activity], 200);
        } else {
            return response()->json(['message' => 'create activity without image'], 400);
        }
    }

    public function show($id)
    {
        $activity = Activity::findOrFail($id);

        $files = File::where('activity_id', $id)->get();
        $images = Image::where('activity_id', $id)->get();

        return response()->json([
            'activity' => $activity,
            'files' => $files,
            'images' => $images
        ], 200);
    }

    public function showAllActivity()
    {
        $activities = Activity::all();
        $activityNames = [];

        foreach ($activities as $activity) {
            $activityNames[] = $activity->name;
        }

        return response()->json([
            'activities' => $activityNames
        ], 200);
    }


    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'videos.*' => 'mimes:mp4,mov,avi,pdf',
            'pdf.*' => 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:2048'
        ]);


        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $activity = Activity::findOrFail($id);

        $activity->update([
            'ss' => $request->ss,
        ]);

        if ($request->hasFile('images')) {
            $files = $request->file('images');

            $activity = $activity ?? new Activity();
            $studentImages = $activity->imageِs()->where('activity_id', $activity->id)->get();

            foreach ($studentImages as $image) {
                Storage::delete($image->path);
                $image->delete();
            }

            $savedImages = Imageable::ssave($files, $activity->id);
        }

        if ($request->hasFile('videos')) {
            $files = $request->file('videos');

            $files = $files ?? new Activity();
            $studentImages = $activity->fileِs()->where('activity_id', $activity->id)->get();

            foreach ($studentImages as $image) {
                Storage::delete($image->path);
                $image->delete();
            }

            $savedImages = Imageable::sssave($files, $activity->id);
        }



        if ($request->hasFile('pdf')) {
            $files = $request->file('pdf');
            $savedFiles = Imageable::ssssave($files, $activity->id);
        }

        return response()->json(['message' => 'Activity updated successfully', 'item' => $activity], 200);
    }

    public function destroy($id)
    {

        $userRole = auth()->user()->role_id;
        if ($userRole !== 1 && $userRole !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $activity = Activity::findOrFail($id);

        $images = $activity->imageِs;
        foreach ($images as $image) {

            $image->delete();
        }

        $files = $activity->fileِs;
        foreach ($files as $file) {

            $file->delete();
        }


        $activity->delete();

        return response()->json(['message' => 'Activity deleted successfully'], 200);
    }

}
