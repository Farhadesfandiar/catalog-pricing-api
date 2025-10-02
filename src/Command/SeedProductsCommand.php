<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\Connection;

#[AsCommand(
  name: 'app:seed-products',
  description: 'Seed the products table with the given products',
)]
class SeedProductsCommand extends Command
{
  public function __construct(private readonly Connection $db)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this
      ->addOption('if-empty', null, InputOption::VALUE_NONE, 'Seed only if products table is empty')
    ;
  }

  /**
   * @return list<array{
   *   sku: string,
   *   name: string,
   *   category: string,
   *   price: int
   * }>
   */
  private function seed(): array
  {
    return [
      ['sku' => '000001', 'name' => 'BV Lean leather ankle boots', 'category' => 'boots', 'price' => 89000],
      ['sku' => '000002', 'name' => 'BV Lean leather ankle boots', 'category' => 'boots', 'price' => 99000],
      ['sku' => '000003', 'name' => 'Ashlington leather ankle boots', 'category' => 'boots', 'price' => 71000],
      ['sku' => '000004', 'name' => 'Naima embellished suede sandals', 'category' => 'sandals', 'price' => 79500],
      ['sku' => '000005', 'name' => 'Nathane leather sneakers', 'category' => 'sneakers', 'price' => 59000],
      ['sku' => '000006', 'name' => 'John Lobb', 'category' => 'sandals', 'price' => 151000],
      ['sku' => '000007', 'name' => 'Brioni', 'category' => 'boots', 'price' => 140000],
    ];
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    if ($input->getOption('if-empty')) {
      // Matches the mapped table name used by Doctrine entity: product
      $count = (int) $this->db->fetchOne('SELECT COUNT(*) FROM product');
      if ($count > 0) {
        $output->writeln('<info>products table is not empty, skipping.</info>');
        return Command::SUCCESS;
      }
    }

    $sql = <<<SQL
  INSERT INTO product (sku, name, category, price)
  VALUES (:sku, :name, :category, :price)
  ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    price = VALUES(price)
  SQL;

    $this->db->beginTransaction();
    try {
      $stmt = $this->db->prepare($sql);
      foreach ($this->seed() as $r) {
        $stmt->executeStatement([
          'sku' => $r['sku'],
          'name' => $r['name'],
          'category' => $r['category'],
          'price' => $r['price'],
        ]);
      }
      $this->db->commit();
    } catch (\Throwable $e) {
      $this->db->rollBack();
      $output->writeln('<error>Seeding failed: ' . $e->getMessage() . '</error>');
      return Command::FAILURE;
    }

    $output->writeln('<info>Seeded products (idempotent)</info>');
    return Command::SUCCESS;
  }
}
