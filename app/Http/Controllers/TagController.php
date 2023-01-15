<?php

namespace App\Http\Controllers;

use App\Http\Exeptions\BadRequestException;
use App\Http\Exeptions\NotFoundException;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.jwt')->except('index');
  }

  public function index(): JsonResponse
  {
    try {
      return response()->json(TagResource::collection(Tag::all()));
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function show($id): JsonResponse
  {
    try {
      if (!$tag = Tag::find($id)) {
        throw new NotFoundException('Tag not found');
      }
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    }
    return response()->json(new TagResource($tag));
  }

  public function store(Request $request): JsonResponse
  {
    try {
      $this->authorize('create', Tag::class);
      $validator = validator($request->all(), [
        'name' => 'required|string|max:255'
      ]);
      if ($validator->fails()) {
        throw new BadRequestException($validator->errors());
      }
      $tag = Tag::create([
        'name' => $request->input('name')
      ]);
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    } catch (BadRequestException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
    return response()->json(new TagResource($tag), 201);
  }

  public function update(Request $request, $id): JsonResponse
  {
    try {
      $this->authorize('update', Tag::class);
      $validator = validator($request->all(), [
        'name' => 'nullable|string|max:255'
      ]);
      if ($validator->fails()) {
        throw new BadRequestException($validator->errors());
      }
      if (!$tag = Tag::find($id)) {
        throw new NotFoundException('Tag not found');
      }
      $tag->update([
        'name' => $request->input('name') ?? $tag->name
      ]);
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    } catch (BadRequestException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    }
    return response()->json(new TagResource($tag));
  }

  public function destroy($id): JsonResponse
  {
    try {
      $this->authorize('delete', Tag::class);
      if (!$tag = Tag::find($id)) {
        throw new NotFoundException('Tag not found');
      }
      $tag->delete();
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
    return response()->json(new TagResource($tag));
  }
}
