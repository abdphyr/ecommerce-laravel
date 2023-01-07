<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
  public function index()
  {
    try {
      return TagResource::collection(Tag::all());
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
  }

  public function show($id)
  {
    try {
      if (!$tag = Tag::find($id)) {
        return response()->json(['error' => 'Tag not found'], 404);
      }
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return new TagResource($tag);
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255'
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      $tag = Tag::create([
        'name' => $request->input('name')
      ]);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return new TagResource($tag);
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'nullable|string|max:255'
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
    try {
      if (!$tag = Tag::find($id)) {
        return response()->json(['error' => 'Tag not found'], 404);
      }
      $tag->update([
        'name' => $request->input('name') ?? $tag->name
      ]);
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return new TagResource($tag);
  }

  public function destroy($id)
  {
    try {
      if (!$tag = Tag::find($id)) {
        return response()->json(['error' => 'Tag not found'], 404);
      }
      $tag->delete();
    } catch (\Throwable $th) {
      return response()->json(['error' => $th->getMessage()], 500);
    }
    return new TagResource($tag);
  }
}
