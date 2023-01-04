<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
  public function index()
  {
    try {
      $categories = CategoryResource::collection(Category::all());
      return $categories;
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
  }

  public function show($id)
  {
    try {
      $category = new CategoryResource(Category::find($id));
      if (!$category) {
        return response()->json(['error' => 'Category not found'], 404);
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return new CategoryResource($category);
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'info' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      $category = Category::create([
        'name' => $request->get('name'),
        'info' => $request->get('info')
      ]);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return new CategoryResource($category);
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'nullable|string|max:255',
      'info' => 'nullable|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      $category = Category::find($id);
      if (!$category) {
        return response()->json(['error' => 'Category not found'], 404);
      }
      $category->update([
        'name' => $request->get('name') ?? $category->name,
        'info' => $request->get('info') ?? $category->info
      ]);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return new CategoryResource($category);
  }

  public function destroy($id)
  {
    try {
      $category = Category::find($id);
      $category->delete();
      if (!$category) {
        return response()->json(['error' => 'Category not found'], 404);
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return new CategoryResource($category);
  }
}
