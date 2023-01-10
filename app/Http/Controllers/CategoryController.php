<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
  public function index(): JsonResponse
  {
    try {
      return response()->json(CategoryResource::collection(Category::all()));
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
  }

  public function show($id): JsonResponse
  {
    try {
      if (!$category = Category::find($id)) {
        return response()->json(['error' => 'Category not found'], 404);
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return response()->json(new CategoryResource($category));
  }

  public function store(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'info' => 'required|string',
      'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      $category = Category::create([
        'name' => $request->input('name'),
        'info' => $request->input('info')
      ]);
      if ($image = $request->file('image')) {
        $name = $image->getClientOriginalName();
        $url = $image->storeAs('products', $name, 'public');
        Image::create([
          'url' => $url,
          'imageable_id' => $category->id,
          'imageable_type' => Category::class
        ]);
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return response()->json(new CategoryResource($category));
  }

  public function update(Request $request, $id): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'name' => 'nullable|string|max:255',
      'info' => 'nullable|string',
      'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      if (!$category = Category::find($id)) {
        return response()->json(['error' => 'Category not found'], 404);
      }
      $category->update([
        'name' => $request->input('name') ?? $category->name,
        'info' => $request->input('info') ?? $category->info
      ]);
      if ($image = $request->file('image')) {
        Storage::disk('public')->delete($category->image->url);
        $category->image()->delete();
        $name = $image->getClientOriginalName();
        $url = $image->storeAs('products', $name, 'public');
        Image::create([
          'url' => $url,
          'imageable_id' => $category->id,
          'imageable_type' => Category::class
        ]);
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return response()->json(new CategoryResource($category));
  }

  public function destroy($id): JsonResponse
  {
    try {
      if (!$category = Category::find($id)) {
        return response()->json(['error' => 'Category not found'], 404);
      }
      Storage::disk('public')->delete($category->image->url);
      $category->image()->delete();
      $category->delete();
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return response()->json(new CategoryResource($category));
  }
}
