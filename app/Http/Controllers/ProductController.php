<?php

namespace App\Http\Controllers;

use App\Http\Exeptions\BadRequestException;
use App\Http\Exeptions\NotFoundException;
use App\Http\Resources\ProductResource;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.jwt')->except(['index', 'show']);
  }
  public function index(Request $request): JsonResponse
  {
    try {
      $query = Product::query();
      if ($search = $request->input('search')) {
        $query = $query->where('name', 'like', "%$search%")
          ->orWhere('info', 'like', "%$search%");
      }
      if ($price = $request->input('price')) {
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
      if ($category = $request->input('category')) {
        $query = $query->whereRelation('category', 'name', $category);
      }
      if ($tag = $request->input('tag')) {
        $query = $query->whereRelation('tags', 'name', $tag);
      }
      if ($sort = $request->input('sort')) {
        if (strpos($sort, ':')) {
          $directives = ['up' => 'asc', 'down' => 'desc'];
          [$column, $directive] = explode(":", $sort);
          $query = $query->orderBy($column, $directives[$directive]);
        } else {
          $query = $query->orderBy($sort);
        }
      }
      $products = $query->latest()->paginate(10);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return response()->json(ProductResource::products($products));
  }

  public function show($id): JsonResponse
  {
    try {
      if (!$product = Product::find($id)) {
        throw new NotFoundException('Product not found');
      }
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    }
    return response()->json(ProductResource::product($product));
  }

  public function store(Request $request): JsonResponse
  {
    try {
      $validator = validator($request->all(), [
        'category_id' => 'required|integer',
        'name' => 'required|string|max:255',
        'price' => 'required|integer',
        'info' => 'required|string',
        'tags' => 'nullable|array',
        'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);
      if ($validator->fails()) {
        throw new BadRequestException($validator->errors());
      }
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
    } catch (BadRequestException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
    return response()->json(ProductResource::product($product));
  }

  public function update(Request $request, $id)
  {
    try {
      $validator = validator($request->all(), [
        'category_id' => 'nullable|integer',
        'name' => 'nullable|string|max:255',
        'price' => 'nullable|integer',
        'info' => 'nullable|string',
        'tags' => 'nullable|array',
        'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);
      if ($validator->fails()) {
        throw new BadRequestException($validator->errors());
      }
      if (!$product = Product::find($id)) {
        throw new NotFoundException('Product not found');
      }
      $this->authorize('update', $product);
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
        Storage::disk('public')->delete($product->images->map(fn ($image) => $image->url)->all());
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
    } catch (BadRequestException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
    return response()->json(ProductResource::product($product));
  }

  public function destroy($id): JsonResponse
  {
    try {
      if (!$product = Product::find($id)) {
        throw new NotFoundException('Product not found');
      }
      $this->authorize('delete', $product);
      Storage::disk('public')->delete($product->images->map(fn ($image) => $image->url)->all());
      $product->images()->delete();
      $product->delete();
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return response()->json(ProductResource::product($product));
  }
}
