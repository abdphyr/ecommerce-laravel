<?php

namespace App\Http\Controllers;

use App\Http\Exeptions\BadRequestException;
use App\Http\Exeptions\NotFoundException;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
  public function __construct()
  {
    $this->middleware(['auth.jwt', 'role:admin'])->except('index');
  }
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
        throw new NotFoundException('Category not found');
      }
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    }
    return response()->json(new CategoryResource($category));
  }

  public function store(Request $request): JsonResponse
  {
    try {
      $this->authorize('create', Category::class);
      $validator = validator($request->all(), [
        'name' => 'required|string|max:255',
        'info' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);
      if ($validator->fails()) {
        throw new BadRequestException($validator->errors());
      }
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
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    } catch (BadRequestException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
    return response()->json(new CategoryResource($category), 201);
  }

  public function update(Request $request, $id)
  {
    try {
      $this->authorize('update', Category::class);
      $validator = validator($request->all(), [
        'name' => 'nullable|string|max:255',
        'info' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);
      if ($validator->fails()) {
        throw new BadRequestException($validator->errors());
      }
      if (!$category = Category::find($id)) {
        throw new NotFoundException('Category not found');
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
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    } catch (BadRequestException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
    return response()->json(new CategoryResource($category));
  }

  public function destroy($id): JsonResponse
  {
    try {
      $this->authorize('delete', Category::class);
      return response()->json(['ok']);
      if (!$category = Category::find($id)) {
        throw new NotFoundException('Category not found');
      }
      Storage::disk('public')->delete($category->image->url);
      $category->image()->delete();
      $category->delete();
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return response()->json(new CategoryResource($category));
  }
}
