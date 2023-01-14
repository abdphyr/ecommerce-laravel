<?php

namespace App\Http\Controllers;

use App\Http\Exeptions\BadRequestException;
use App\Http\Exeptions\NotFoundException;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.jwt')->except('index');
  }
  public function index(Request $request)
  {
    try {
      if (!$product = Product::find($request->input('product_id'))) {
        throw new NotFoundException('Product not found');
      }
      $comments = CommentResource::collection($product->comments);
      $count = $product->comments()->count();
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    }
    return response()->json(compact('comments', 'count'));
  }

  public function show($id): JsonResponse
  {
    try {
      if (!$comment = Comment::find($id)) {
        throw new NotFoundException('Comment not found');
      }
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    }
    return response()->json(new CommentResource($comment));
  }

  public function store(Request $request): JsonResponse
  {
    try {
      $validator = validator($request->all(), [
        'product_id' => 'required|integer',
        'body' => 'required|string'
      ]);
      if ($validator->fails()) {
        throw new BadRequestException($validator->errors());
      }
      $newComment = Comment::create([
        "user_id" => auth()->user()->id,
        "product_id" => $request->input('product_id'),
        "body" => $request->input('body')
      ]);
    } catch (BadRequestException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
    return response()->json(new CommentResource($newComment), 201);
  }

  public function update(Request $request, $id)
  {
    try {
      $validator = validator($request->all(), [
        'body' => 'nullable|string'
      ]);
      if ($validator->fails()) {
        throw new BadRequestException($validator->errors());
      }
      if (!$comment = Comment::find($id)) {
        throw new NotFoundException('Comment not found');
      }
      $this->authorize('update', $comment);
      $comment->update([
        'body' => $request->input('body') ?? $comment->body
      ]);
    } catch (BadRequestException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
    return response()->json(new CommentResource($comment));
  }

  public function destroy($id): JsonResponse
  {
    try {
      if (!$comment = Comment::find($id)) {
        throw new NotFoundException('Comment not found');
      }
      $this->authorize('delete', $comment);
      $comment->delete();
    } catch (NotFoundException $e) {
      return response()->json($e->getError(), $e->getCode());
    } catch (AuthorizationException $e) {
      return response()->json(['error' => $e->getMessage()], 403);
    }
    return response()->json(new CommentResource($comment));
  }
}
