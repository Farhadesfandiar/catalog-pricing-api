<?php
declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Product\Category;
use App\Domain\Product\ProductRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\ProductEntity;
use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Doctrine\Mapper\ProductMapper;
use Doctrine\ORM\AbstractQuery;

final class DoctrineProductRepository implements ProductRepositoryInterface
{
  public function __construct(private EntityManagerInterface $em)
  {
  }

  public function findByFilters(?Category $category, ?int $priceLessThan, int $limit): iterable
  {
    $qb = $this->em->createQueryBuilder()
      ->select('p')
      ->from(ProductEntity::class, 'p')
      ->orderBy('p.sku', 'ASC')
      ->setMaxResults($limit);

    if ($category) {
      $qb->andWhere('p.category = :cat')->setParameter('cat', $category);
    }
    if ($priceLessThan) {
      $qb->andWhere('p.price <= :max')->setParameter('max', $priceLessThan);
    }

    foreach ($qb->getQuery()->toIterable([], AbstractQuery::HYDRATE_OBJECT) as $p) {
      yield ProductMapper::toDomain( $p);
    }
  }
}




