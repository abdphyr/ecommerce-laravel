<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
  private static bool $collect = false;


  public static function products($products)
  {
    self::$collect = true;
    return self::collection($products);
  }

  public static function product($product)
  {
    self::$collect = false;
    return new ProductResource($product);
  }


  public function toArray($request)
  {
    if (self::$collect) {
      return [
        "id" => $this->id,
        "user_id" => $this->user_id,
        "name" => $this->name,
        "price" => $this->price,
        "info" => $this->info,
        "created_at" => date('Y-m-d', strtotime($this->created_at)),
        "category_name" => $this->category->name,
        "tags" => TagResource::collection($this->tags),
        "image" => ImageResource::collection($this->images)[0] ?? null,
      ];
    } else {
      return [
        "id" => $this->id,
        "user_id" => $this->user_id,
        "name" => $this->name,
        "price" => $this->price,
        "info" => $this->info,
        "created_at" => date('Y-m-d', strtotime($this->created_at)),
        "images" => ImageResource::collection($this->images),
        "tags" => TagResource::collection($this->tags),
        "category" => new CategoryResource($this->category),
        "user" => new UserResource($this->user),
      ];
    }
  }
}
