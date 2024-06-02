<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function create(Request $request){

    $ss = Category::create([
        'name'=>$request->name,
    ]);
    return response()->json(['ss'=>$ss]);
}
}
