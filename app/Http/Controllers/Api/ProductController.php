<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\ProductRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

final class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/products',
        summary: 'Product catalogue',
        tags: ['Catalogue'],
        responses: [new OA\Response(response: 200, description: 'Products')],
        x: ['unauthenticated' => true],
    )]
    public function index(ProductRepository $products): AnonymousResourceCollection
    {
        return ProductResource::collection($products->allActive());
    }
}
