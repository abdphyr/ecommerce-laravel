<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
  public function index(Request $request)
  {
    $category = $request->input('category');
    $tag = $request->input('tag');
    $price = $request->input('price');
    $sort = $request->input('sort');
    $search = $request->input('search');
    try {
      $query = Product::query();
      if($search){
        $query = $query->where('name', 'like', "%$search%")
          ->orWhere('info', 'like', "%$search%");
      }
      if ($price) {
        $interval = explode(":", $price);
        if ($interval[0] === '') {
          $query = $query->where("price", '<=', $interval[1]);
        } else if ($interval[1] === '') {
          $query = $query->where("price", '>=', $interval[0]);
        } else {
          $query = $query->where("price", '>=', $interval[0])
            ->where("price", '<=', $interval[1]);
        }
      }
      if ($category) {
        $query = $query->whereRelation('category', 'name', $category);
      }
      if ($tag) {
        $query = $query->whereRelation('tags', 'name', $tag);
      }
      if ($sort) {
        if (strpos($sort, ':')) {
          $directives = ['up' => 'asc', 'down' => 'desc'];
          [$column, $directive] = explode(":", $sort);
          $query = $query->orderBy($column, $directives[$directive]);
        } else {
          $query = $query->orderBy($sort);
        }
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return ProductResource::products($query->latest()->paginate(10));
  }

  public function show($id)
  {
    try {
      $product = Product::find($id);
      if (!$product) {
        return response()->json(['error' => 'Product not found'], 404);
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return ProductResource::product($product);
  }


  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'category_id' => 'required|integer',
      'name' => 'required|string|max:255',
      'price' => 'required|integer',
      'info' => 'required|string',
      'tags' => 'nullable|array',
      'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      $product = Product::create([
        'user_id' => $request->user()->id,
        'category_id' => $request->category_id,
        'name' => $request->input('name'),
        'price' => $request->input('price'),
        'info' => $request->input('info')
      ]);
      $product->tags()->attach($request->tags ?? []);
      if ($images = $request->file('images')) {
        foreach ($images as $image) {
          $name = $image->getClientOriginalName();
          $url = $image->storeAs('products', $name, 'public');
          Image::create([
            'url' => $url,
            'imageable_id' => $product->id,
            'imageable_type' => Product::class
          ]);
        }
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return ProductResource::product($product);
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'category_id' => 'nullable|integer',
      'name' => 'nullable|string|max:255',
      'price' => 'nullable|integer',
      'info' => 'nullable|string',
      'tags' => 'nullable|array',
      'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      $product = Product::find($id);
      if (!$product) {
        return response()->json(['error' => 'Product not found'], 404);
      }
      $product->update([
        'category_id' => $request->input('category_id') ?? $product->cretgory_id,
        'name' => $request->input('name') ?? $product->name,
        'price' => $request->input('price') ?? $product->price,
        'info' => $request->input('info') ?? $product->info
      ]);
      if ($request->tags) {
        $product->tags()->detach();
        $product->tags()->attach($request->tags);
      }
      if ($images = $request->file('images')) {
        Storage::delete($product->images->map(fn ($image) => $image->url)->all());
        $product->images()->delete();
        foreach ($images as $image) {
          $name = $image->getClientOriginalName();
          $url = $image->storeAs('products', $name, 'public');
          Image::create([
            'url' => $url,
            'imageable_id' => $product->id,
            'imageable_type' => Product::class
          ]);
        }
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return ProductResource::product($product);
  }

  public function destroy($id)
  {
    try {
      $product = Product::find($id);
      if (!$product) {
        return response()->json(['error' => 'Product not found'], 404);
      }
      Storage::delete($product->images->map(fn ($image) => $image->url)->all());
      $product->images()->delete();
      $product->delete();
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return new ProductResource($product);
  }
}
