<?php

namespace App\Traits;
use App\Models\File;
use App\Models\Image;
use App\Models\Image_child;
use App\Models\Image_student;


trait Imageable
{
    public static function ssave($getImages,$id): void
    {
        foreach($getImages as $getImage) {
            $filename = $getImage->getClientOriginalName();
            $name = pathinfo($filename, PATHINFO_FILENAME) . '' . time() . '.' . $getImage->getClientOriginalExtension();
            $path = 'activities';
            $getImage->move($path, $name);
            $save = Image::create([
                'name' => $name,
                'path' => $path,
                'activity_id' => $id
            ]);
        }
    }

    public static function ssave_c($getImages,$id): void
    {
        foreach($getImages as $getImage) {
            $filename = $getImage->getClientOriginalName();
            $name = pathinfo($filename, PATHINFO_FILENAME) . '' . time() . '.' . $getImage->getClientOriginalExtension();
            $path = 'students';
            $getImage->move($path, $name);
            $save = Image_child::create([
                'name' => $name,
                'path' => $path,
                'student_id' => $id
            ]);
        }
    }

    public static function ssave_m($getImages,$id): void
    {
        foreach($getImages as $getImage) {
            $filename = $getImage->getClientOriginalName();
            $name = pathinfo($filename, PATHINFO_FILENAME) . '' . time() . '.' . $getImage->getClientOriginalExtension();
            $path = 'students';
            $getImage->move($path, $name);
            $save = Image_student::create([
                'name' => $name,
                'path' => $path,
                'student_id' => $id
            ]);
        }
    }

    public static function sssave($getFiles, $id): void
    {
        foreach ($getFiles as $getFile) {
            $filename = $getFile->getClientOriginalName();
            $extension = $getFile->getClientOriginalExtension();
            $name = pathinfo($filename, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;
            $path = 'videos';

            $getFile->move($path, $name);

            $save = File::create([
                'name' => $name,
                'path' => $path,
                'extension' => $extension,
                'activity_id' => $id
            ]);
        }
    }

    public static function ssssave($getFiles, $id): void
    {
        foreach ($getFiles as $getFile) {
            $filename = $getFile->getClientOriginalName();
            $extension = $getFile->getClientOriginalExtension();
            $name = pathinfo($filename, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;
            $path = 'pdf';

            $getFile->move($path, $name);

            $save = File::create([
                'name' => $name,
                'path' => $path,
                'extension' => $extension,
                'activity_id' => $id
            ]);
        }
    }

    public static  function ss($getImage){
    $filename=$getImage->getClientOriginalName();
    $name = pathinfo($filename, PATHINFO_FILENAME).''.time().'.'.$getImage->extension();
    $path = 'users';
    $getImage->move($path, $name);
    return $name;
}
    public static  function sss($getImage){
        $filename=$getImage->getClientOriginalName();
        $name = pathinfo($filename, PATHINFO_FILENAME).''.time().'.'.$getImage->extension();
        $path = 'comments';
        $getImage->move($path, $name);
        return $name;
    }
    public static  function ssss($getImage){
        $filename=$getImage->getClientOriginalName();
        $name = pathinfo($filename, PATHINFO_FILENAME).''.time().'.'.$getImage->extension();
        $path = 'products';
        $getImage->move($path, $name);
        return $name;
    }




}

