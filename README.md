## Product Filter API
REST API that lists products with **category/SKU discount rules** applied and supports filtering.
Tech stack and features:
- **Domain-centric architecture**.
- **Symfony 6.4**
- **PHP 8.4**
- **MySQL (Doctrine)**
- **Dockerized** 
  - Nginx
  - PHP
  - MySQL
  - Adminer
--------------

Here are the rules, simplified:
- Prices are **integers in cents** (e.g., €100.00 → 10000).
- **Discount rules** can be applied on product prices.
- If multiple discounts apply, the **highest** one wins.
- The API returns **at most 5** products.
- Products can be **filtered** based on the **category** and **priceLessThan** filters. 

## How To Run
### Prereqs
- **Docker & Docker Compose**
- **GNU Make** — *used for building, running, and managing common tasks (e.g., Docker build/up, DB migrations, seeding, tests) with a single command.*

  **Install Make:**
  - **macOS:** `xcode-select --install` (or `brew install make`; may be called `gmake`)
  - **Ubuntu/Debian:** `sudo apt-get update && sudo apt-get install -y make`
  - **Fedora:** `sudo dnf install make`
  - **Windows:** `choco install make` (or with MSYS2: `pacman -S make`)

- **Curl**, **Postman**, or a **web browser** (for making API requests)

### Run the App
 In the root directory of the project run the below command.It builds and runs the application:
**`make app-build-up`**
The above command peforms the below steps:
- builds the image
- runs containers 
- installs composer packages
- do the migration (if needed)
- seed the database (if needed)


Now the API request can be sent to the endpoint below:

<sub><span style="color: gray;">All products (max 5)</span></sub>

`curl 'http://localhost:8080/products'`


<sub><span style="color: gray;"># Filter by category</span></sub>

`curl 'http://localhost:8080/products?category=boots'`

<sub><span style="color: gray;"># Filter by original price (in cents)</span></sub>

`curl 'http://localhost:8080/products?priceLessThan=80000'`

<sub><span style="color: gray;"># Category + price</span></sub>

`curl 'http://localhost:8080/products?category=boots&priceLessThan=80000'`

### Test
- Runs both integration and unit tests with `make test`

- Static analysis is enabled at level 8 with PHPStan and can be run: `make test-phpstan`

In case of setting a value for API_KEY in the .env file, the authentication security will be activated for the API!

## Logic Structure
```
src/
├─ Controller/
│  └─ ProductsController.php                   # GET /products (thin; validates;)
├─ Application/
│  ├─ GetProductsQuery.php                     # Input DTO 
│  └─ GetProductsHandler.php                   # Orchestrate the logic
├─ Domain/
│  ├─ Product/
│  │  ├─ Product.php                           # sku, name, Category, Price(original)
│  │  ├─ Price.php                             # discounted(int %)
│  │  ├─ Category.php                          
│  │  └─ ProductRepositoryInterface.php        # port;
│  └─ Discount/
│     ├─ DiscountRuleInterface.php            
│     ├─ CategoryDiscountRule.php              
│     ├─ SkuDiscountRule.php                  
│     ├─ PriceInfo.php                         
│     ├─ DiscountServiceInterface.php
│     └─ DiscountService.php                   # picks max applicable rule
├─ Infrastructure/
│  ├─ Discount/
│  │  └─ DiscountRulesFactory.php              # reads rules from discounts.yaml & builds the DiscountService
│  ├─ Doctrine/
│  │  ├─ Entity/
│  │  │  └─ ProductEntity.php                  # storage model
│  │  ├─ Mapper/
│  │  │  └─ ProductMapper.php                  # ProductEntity → Domain\Product
│  │  └─ Repository/
│  │     └─ DoctrineProductRepository.php      # Port to doctrine database (for app)
│  └─ InMemory/
│        └─ InMemoryProductRepository.php      # port to in-memory data (for tests; no I/O)
```
## Domain
I kept the domain models (src/Domain/\*) separated from the persistence models (src/Infrastructure/Doctrine/Entity/\*)
- The goal is to keep the logics pure, composable, and testable
- Everything here is pure PHP (no I/O), so unit tests are fast and deterministic.
### Product
**Product** holds: sku, name, Category, and original Price.

**Price** compute the discount

We do not persist final or discount_percentage. They’re derived via the discount domain.

### Discount
- Adding new discount rules without touching existing ones. Interfaces are implemented here. Add new discounts by adding new rule classes; no switch/case anywhere.
- Discounts are modeled as independent rules. Each rule looks at a Product and either says “not me” or returns a percentage (1..100).
- The app wires which rules are active via configuration(**Discounts.yaml**). It is plugeable, so the rules can be read from the DB in future.
- Discounts and percentages are computed on the fly (not stored). Avoiding the risk of inconsistent values whenever rules or original prices change. It also helps performance: priceLessThan filters by the indexed original price, so the database can use its index efficiently.

## Data Layer
The data persistance layer is plugeable and can be swapped per environment or storage model.

### Doctrine (MySQL):
ProductEntity: maps to table product(sku, name, category, price)
Indexes: id, sku, category, and price
Fetching is capped with `LIMIT 5` to satisfy requirements and ensure predictable performance.

### InMemory (Tests)
The InMemoryProductiveRepository class is intended for testing; it is initialized from an array and does not rely on the network or filesystem.

## Application
- Controller
  - validate inputes 
  - call handler by passing a DTO (immutable inputs, decoupling)
- Handler
  - orchestrates repository + discounts

## API
`GET /products`

**Quety params**
 - category — slug ([a-z0-9_-]+)
 - priceLessThan — integer cents (e.g., 2000 for €20.00)

**Response** sample:
```json
[
  {
    "sku": "000001",
    "name": "BV Lean leather ankle boots",
    "category": "boots",
    "price": {
      "original": 89000,
      "final": 62300,
      "discount_percentage": "30%",
      "currency": "EUR"
    }
  }
]
```

## License
MIT
