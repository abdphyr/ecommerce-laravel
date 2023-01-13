<?php

namespace App\Http\Controllers;

use App\Http\Exeptions\BadRequestException;
use App\Http\Exeptions\NotFoundException;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
  public function index(Request $request)
  {
    try {
      if (!$product = Product::find($request->input('product_id'))) {
        throw new NotFoundException('Product not found');
      }
      $comments = CommentResource::collection($product->comments);
      $count = $product->comments()->count();
    } catch (NotFoundException $e) {
      return response()->json(['error' => $e->getMessage()], $e->getCode());
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
      return response()->json(['error' => $e->getMessage()], $e->getCode());
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
      return response()->json(['error' => json_decode($e->getMessage())], 400);
    } catch (\Throwable $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
    return response()->json(new CommentResource($newComment));
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
      $comment->update([
        'body' => $request->input('body') ?? $comment->body
      ]);
    } catch (BadRequestException $e) {
      return response()->json(['error' => json_decode($e->getMessage())], 400);
    } catch (NotFoundException $e) {
      return response()->json(['error' => $e->getMessage()], 404);
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
      $comment->delete();
    } catch (NotFoundException $th) {
      return response()->json(['error' => $th->getMessage()], $th->getCode());
    }
    return response()->json(new CommentResource($comment));
  }
}
