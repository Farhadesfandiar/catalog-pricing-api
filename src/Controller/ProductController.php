<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Application\GetProductsHandler;
use App\Domain\Product\Category;
use App\Application\GetProductsQuery;

final class ProductController extends AbstractController
{
  public function __construct(private GetProductsHandler $handler)
  {
  }

  #[Route('/products', name: 'product_list', methods: ['GET'])]
  public function list(Request $request): JsonResponse
  {
    $rawCategory = $request->query->get('category');
    $rawPriceLT = $request->query->get('priceLessThan');

    $category = null;
    if ($rawCategory !== null && $rawCategory !== '') {
      if (!preg_match('/^[a-z0-9_-]+$/', $rawCategory)) {
        return $this->json(['error' => 'Category must be a slug (a-z, 0-9, -, _)'], 400);
      }

      $category = new Category($rawCategory);
    }

    $priceLT = null;
    if ($rawPriceLT !== null && $rawPriceLT !== '') {
      if (!ctype_digit((string) $rawPriceLT)) {
        return $this->json(['error' => 'Price less than must be an integer number of cents (e.g. 2000 for 20,00€)'], 400);
      }

      $priceLT = (int) $rawPriceLT;
    }

    $products = ($this->handler)(new GetProductsQuery($category, $priceLT, 5));

    return new JsonResponse($products);
  }
}
