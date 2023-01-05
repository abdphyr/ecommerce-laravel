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
  public function index()
  {
    try {
      $products = ProductResource::products(Product::latest()->paginate(1));
      return $products;
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
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
        'name' => $request->get('name'),
        'price' => $request->get('price'),
        'info' => $request->get('info')
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
        'category_id' => $request->get('category_id') ?? $product->cretgory_id,
        'name' => $request->get('name') ?? $product->name,
        'price' => $request->get('price') ?? $product->price,
        'info' => $request->get('info') ?? $product->info
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
