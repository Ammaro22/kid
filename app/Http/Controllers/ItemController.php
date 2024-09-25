<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ItemController extends Controller
{
    public function createItem(Request $request)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_description' => 'nullable|string',
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,svg',
        ]);


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $newImage = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('upload'), $newImage);
            $path = "upload/$newImage";
        } else {
            $path = null;
        }


        $validatedData['image'] = $path;


        $item = Item::create($validatedData);

        return response()->json([
            'message' => 'Item created successfully',
            'item' => $item,
        ], 201);
    }

    public function updateItem(Request $request, $id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }


        $validatedData = $request->validate([
            'item_name' => 'nullable|string|max:255',
            'item_description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);


        if ($request->hasFile('image')) {

            if ($item->image) {
                @unlink(public_path($item->image));
            }

            $image = $request->file('image');
            $newImage = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('upload'), $newImage);
            $validatedData['image'] = "upload/$newImage";
        }

        foreach ($validatedData as $key => $value) {
            if ($value !== null) {
                $item->$key = $value;
            }
        }

        $item->save();

        return response()->json([
            'message' => 'Item updated successfully',
            'item' => $item,
        ], 200);
    }

    public function getItems(Request $request)
    {
        try {
            $items = Item::all();


            return response()->json([
                'success' => true,
                'message' => 'Items retrieved successfully.',
                'data' => $items,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving items: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteItem($id)
    {
        $userRole = auth()->user()->role_id;
        if ($userRole !== 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        if ($item->image) {
            @unlink(public_path($item->image));
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully.',
        ], 200);
    }

    /*عرض البنود بالنسبة للاهل*/
    public function getItemsforparent(Request $request)
    {

        try {

            $user = auth()->user();
            $userRole = auth()->user()->role_id;
            if ($userRole !== 4) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $student = $user->Student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'No student associated with this user.',
                ], 404);
            }


            $items = Item::all();

            return response()->json([
                'success' => true,
                'message' => 'Items retrieved successfully.',
                'data' => $items,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving items: ' . $e->getMessage(),
            ], 500);
        }
    }
}
