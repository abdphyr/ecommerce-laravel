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
      return $category;
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
  }

  public function store(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'info' => 'required|string',
      ]);
      if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
      }
      $category = Category::create([
        'name' => $request->get('name'),
        'info' => $request->get('info')
      ]);
      return new CategoryResource($category);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
  }

  public function update(Request $request, $id)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'nullable|string|max:255',
        'info' => 'nullable|string',
      ]);
      if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
      }
      $category = Category::find($id);
      if (!$category) {
        return response()->json(['error' => 'Category not found'], 404);
      }
      $category->update([
        'name' => $request->get('name') ?? $category->name,
        'info' => $request->get('info') ?? $category->info
      ]);
      return new CategoryResource($category);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $category = Category::find($id);
      if (!$category) {
        return response()->json(['error' => 'Category not found'], 404);
      }
      $category->delete();
      return new  CategoryResource($category);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
  }
}
