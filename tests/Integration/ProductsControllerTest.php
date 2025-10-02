<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProductsControllerTest extends WebTestCase
{
  public function testListProducts(): void
  {
    $client = static::createClient();
    $client->request('GET', '/products?category=boots&priceLessThan=100000');

    $this->assertResponseIsSuccessful();
    $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $this->assertIsArray($data);
    $this->assertLessThanOrEqual(5, count($data));
    $this->assertArrayHasKey('price', $data[0]);
    $this->assertArrayHasKey('final', $data[0]['price']);
  }

  public function testReturnsAtMostFiveProducts(): void
  {
    $client = static::createClient();
    // No filters: should still cap at 5
    $client->request('GET', '/products');

    $this->assertResponseIsSuccessful();
    $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $this->assertIsArray($data);
    $this->assertLessThanOrEqual(5, count($data));
  }

}