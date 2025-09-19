# DevHelm Web Development Guidelines

This document provides essential information for developing the ComControl web application, a Symfony 7.2 + Vue.js 3 project with comprehensive testing setup.

## Project Architecture

The web application is built using:
- **Backend**: Symfony 7.2 with PHP 8.2+
- **Frontend**: Vue.js 3 with Webpack Encore
- **Styling**: Tailwind CSS with PurgeCSS optimization
- **Build System**: Webpack Encore with Babel for transpilation

## Build and Configuration

### Prerequisites
- PHP 8.2 or higher
- Node.js and npm
- Composer

### Initial Setup
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy configuration files (if needed)
cp .env.example .env
cp phpunit.dist.xml phpunit.xml
cp behat.yml.dist behat.yml
```

### Build Commands

#### Development
```bash
# Start the docker environment
docker compose up -d 

# Build assets for development
npm run dev

# Build assets and watch for changes
npm run watch

# Start development server with HMR
npm run dev-server
```

#### Production
```bash
# Build optimized assets for production
npm run build
```

### Webpack Configuration

The `webpack.config.js` uses Symfony Encore with:
- Vue.js loader enabled
- Sass/SCSS support
- Tailwind CSS with PurgeCSS for production optimization
- Source maps in development
- Asset versioning in production

**Important Note**: There's a syntax error in `webpack.config.js` line 33: `pluginsplugins` should be `plugins`.

### Asset Structure
- Entry point: `./assets/app.js`
- Output directory: `public/build/`
- Templates scanned for PurgeCSS: `./templates/**/*.twig`, `./assets/js/**/*.vue`, `./assets/js/**/*.js`

## Testing Framework

The project uses multiple testing frameworks for comprehensive coverage:

### JavaScript Testing (Jest)

#### Configuration
- Config file: `jest.config.js` (minimal configuration with v8 coverage provider)
- Test files location: `assets/services/__tests__/`
- Pattern: `*.test.js` or `*.spec.js`

#### Running JavaScript Tests
```bash
# Run all JavaScript tests
npm test

# Run specific test file
npm test -- assets/services/__tests__/example.test.js

# Run with coverage
npm test -- --coverage
```

#### Example Test Structure
```javascript
describe('Test Suite Name', () => {
    test('test description', () => {
        expect(actual).toBe(expected);
    });
});
```

**Important**: Existing tests use Vitest imports but Jest is configured as the test runner. Use Jest syntax for new tests.

### PHP Unit Testing (PHPUnit)

#### Configuration
- Config file: `phpunit.dist.xml`
- Test directory: `tests/`
- Bootstrap: `tests/bootstrap.php`
- Environment: `APP_ENV=test`

#### Running PHP Tests
```bash
# Start the docker environment
docker compose up -d 

# Run all unit tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Unit/ExampleTest.php

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage/
```

#### Test Structure
```php
<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testExample(): void
    {
        $this->assertTrue(true);
        $this->assertEquals(expected, actual);
    }
}
```

### BDD Testing (Behat)

#### Configuration
- Config file: `behat.yml.dist`
- Features directory: `features/`
- Context classes in: `App\Tests\Behat\`

#### Available Contexts
- `DemoContext`
- `GeneralContext` 
- `UserContext`
- `PlanContext`
- `TeamContext`

#### Running Behat Tests

```bash
# Start up docker
docker compose up -d

# Run all BDD tests
docker compose exec php-fpm vendor/bin/behat

# Dry run to check syntax
docker compose exec php-fpm vendor/bin/behat --dry-run

# Run specific feature
docker compose exec php-fpm vendor/bin/behat features/demo.feature
```

## Code Quality

### PHP CS Fixer
The project uses PHP CS Fixer for code style enforcement:

```bash
# Fix code style (as per git guidelines)
docker compose exec php-fpm vendor/bin/php-cs-fixer fix --allow-unsupported-php-version=yes
```

### PHP Code Standards

* **Use Attributes Over Annotations**: Always prefer PHP 8 attributes over docblock annotations:
    - ✅ DO: `#[Route('/api/users', name: 'api_users')]`
    - ❌ DON'T: `@Route("/api/users", name="api_users")`
    - ✅ DO: `#[Assert\NotBlank]`
    - ❌ DON'T: `@Assert\NotBlank`

  Attributes are natively supported in PHP 8+, provide better type safety, better IDE support, and are part of the language syntax rather than comments.

### Testing Guidelines

1. **JavaScript Tests**: Place in `assets/services/__tests__/` with `.test.js` extension
2. **PHP Unit Tests**: Place in `tests/Unit/` with `Test.php` suffix
3. **Integration Tests**: Use `tests/Integration/` directory
4. **BDD Tests**: Create `.feature` files in `features/` directory
5. **Do Not Test Logic-less Classes**: Do not write tests for entities, DTOs, and other classes that contain no logic. These classes typically only define properties and getters/setters without business logic, making tests redundant and maintenance-heavy. Focus testing efforts on classes that contain actual business logic.

## Architecture
### Value Objects and Enums

* Value Objects and Enums are held within the namespace that they belong to within the purposes of the domain. For example, a Money Value Object would be in the App\Entity namespace as it is used by entities. And an enum representing the status of a Subscription would be in the DevHelm\Subscription namespace.

### Repositories

* The repository pattern that is used throughout this project is documented in repository-pattern.md
* DOCTRINE MUST NOT BE USED OUTSIDE OF THE `App\Repository` NAMESPACE
* When querying entities that only need data from a single entity, use the `findOneBy` and `findBy` methods instead of QueryBuilder. QueryBuilder should only be used for complex queries involving multiple tables/entities.

### DTOs

* The DTOS are held within the App\Dto namespace. They are readonly classes that use the constructor promotion and only contain public members.
* DTOs are organised by endpoint type api, app, and webhook which is decided based on the route of the controller action. And then organised further into Request and Response based upon if they are used to represent the request body or response body. Webhooks may not have a DTO for all endpoints, but API and APP MUST have DTOs for their Request and Response.
* DTOs are to use the Symfony Serializer component. And members are to be snake_case and not camelCase.
* Response DTOs are to be created within the Factory relating to that domain item.
* And Generic will be things such as ListResponse.

Structure:

|- Generic
|- Api
|   | - Request
|   | - Response
|- App
|   | - Request
|   | - Response


### Controllers

* Controllers are organised by endpoint type api, app, and webhook, which is decided based on the route of the controller action.
* Controllers *MUST NOT* use doctrine EntityManager directly and MUST use a repository interface. Dependencies should be injected into the action and not the constructor.
* Controllers are to use the Symfony Serializer component to deserialize request bodies into DTOs and serialize response DTOs into JSON.
* Controllers are to use the Symfony Validator component to validate request DTOs.
* Controllers are to use the Parthenon LoggerTrait for logging.
* Controllers should have dependencies injected into the action method rather than the constructor.
* Controllers MUST log the receipt of requests and key actions taken, including any errors encountered.
* Controllers MUST not be unit tested but tested via functional tests or Behat.

#### File and Class Organization Requirements

* **Single Class Per File**: PHP files MUST contain only a single class. Multiple classes in one file are not permitted:
    - ✅ DO: One controller class per file
    - ❌ DON'T: Multiple controller classes in the same file

* **Controller Action Grouping**: All controller actions for a single entity/domain MUST be grouped into a single controller class:
    - ✅ DO: All agent-related actions (create, list, update, delete) in `AgentController`
    - ❌ DON'T: Separate controller classes for different routes of the same entity (e.g., `AgentController` and `AgentSingleController`)

#### Controller-Specific Logging and User Injection Guidelines

* **Logger Usage with LoggerAwareTrait**: When using `LoggerAwareTrait`, do NOT inject `LoggerInterface` into controller actions. Instead, access the logger via `$this->getLogger()`:
    - ✅ DO: `$this->getLogger()->info('Message');`
    - ❌ DON'T: Inject `LoggerInterface $logger` and use `$this->setLogger($logger);`

* **User Injection**: Always inject the current user using the `#[CurrentUser]` attribute, not by manually retrieving from request attributes:
    - ✅ DO: `#[CurrentUser] User $user` in action parameter
    - ❌ DON'T: `$user = $request->attributes->get('_user');`

* **Team Access**: Do not add unnecessary sanity checks for user team relationships. If the user is authenticated and authorized, assume valid team relationships exist:
    - ✅ DO: `$team = $user->getTeam();` (direct access)
    - ❌ DON'T: `if (!$team instanceof Team) { return new JsonResponse(['error' => 'User must belong to a team'], Response::HTTP_FORBIDDEN); }`

**Structure:**

|- Api
|- App
|- Webhooks


### CRUD Actions

**Create:**
```php
    #[IsGranted('ROLE_LEAD')]
    #[Route('/app/product/{id}/price', name: 'app_product_price_create', methods: ['POST'])]
    public function createPrice(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        PriceRepositoryInterface $priceRepository,
        ProductRepositoryInterface $productRepository,
        PriceFactory $priceFactory,
    ) {
        $this->getLogger()->info('Received request to create price', ['product_id' => $request->get('id')]);

        try {
            /** @var Product $product */
            $product = $productRepository->getById($request->get('id'));
        } catch (NoEntityFoundException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }

        /** @var CreatePrice $dto */
        $dto = $serializer->deserialize($request->getContent(), CreatePrice::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $errorOutput = [];
            foreach ($errors as $error) {
                $propertyPath = $error->getPropertyPath();
                $errorOutput[$propertyPath] = $error->getMessage();
            }

            return new JsonResponse([
                'errors' => $errorOutput,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $price = $priceFactory->createPriceFromDto($dto);
        $price->setProduct($product);

        $priceRepository->save($price);
        $dto = $priceFactory->createAppDto($price);
        $jsonResponse = $serializer->serialize($dto, 'json');

        return new JsonResponse($jsonResponse, JsonResponse::HTTP_CREATED, json: true);
    }
```

**List:**

All CRUD list pages MUST follow this pattern using the CrudRepositoryInterface getList method with proper pagination and filtering:

```php
    #[Route('/app/price', name: 'app_price_list', methods: ['GET'])]
    public function listPrices(
        Request $request,
        PriceRepositoryInterface $priceRepository,
        SerializerInterface $serializer,
        PriceDataMapper $priceFactory,
    ): Response {
        $this->getLogger()->info('Received request to list prices');

        $lastKey = $request->get('last_key');
        $firstKey = $request->get('first_key');
        $resultsPerPage = (int) $request->get('limit', 10);

        if ($resultsPerPage < 1) {
            return new JsonResponse([
                'reason' => 'limit is below 1',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($resultsPerPage > 100) {
            return new JsonResponse([
                'reason' => 'limit is above 100',
            ], JsonResponse::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        // Add filters based on business logic (e.g., team filtering, user access, etc.)
        $filters = [
            // Example: 'team' => $user->getTeam()->getId(),
        ];

        $resultSet = $priceRepository->getList(
            filters: $filters,
            limit: $resultsPerPage,
            lastId: $lastKey,
            firstId: $firstKey,
            sortKey: 'id', // or relevant sorting field
            sortType: 'DESC',
        );

        $dtos = array_map([$priceFactory, 'createAppDto'], $resultSet->getResults());

        $listResponse = new ListResponse();
        $listResponse->setHasMore($resultSet->hasMore());
        $listResponse->setData($dtos);
        $listResponse->setLastKey($resultSet->getLastKey());

        $json = $serializer->serialize($listResponse, 'json');

        return new JsonResponse($json, json: true);
    }
```

**Key Requirements for CRUD List Pattern:**
- MUST use CrudRepositoryInterface getList method with all parameters
- MUST include pagination validation (limit 1-100)
- MUST include proper filtering based on user context
- MUST use firstId, lastId, sortKey, and sortType parameters
- MUST return proper ListResponse with hasMore and lastKey

**Update:**

```php
    #[IsGranted('ROLE_LEAD')]
    #[Route('/app/product/{id}/price/{priceId}/delete', name: 'app_product_price_delete', methods: ['POST'])]
    public function deletePrice(
        Request $request,
        PriceRepositoryInterface $priceRepository,
    ) {
        $this->getLogger()->info('Received request to delete price', ['product_id' => $request->get('id'), 'price_id' => $request->get('priceId')]);

        try {
            /** @var Price $price */
            $price = $priceRepository->findById($request->get('priceId'));
        } catch (NoEntityFoundException $exception) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }

        $price->markAsDeleted();
        $priceRepository->save($price);

        return new JsonResponse([], JsonResponse::HTTP_ACCEPTED);
    }
```

**Delete**
```php
    #[IsGranted('ROLE_LEAD')]
    #[Route('/app/product/{id}/price/{priceId}/delete', name: 'app_product_price_delete', methods: ['POST'])]
    public function deletePrice(
        Request $request,
        PriceRepositoryInterface $priceRepository,
    ) {
        $this->getLogger()->info('Received request to delete price', ['product_id' => $request->get('id'), 'price_id' => $request->get('priceId')]);

        try {
            /** @var Price $price */
            $price = $priceRepository->findById($request->get('priceId'));
        } catch (NoEntityFoundException $exception) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }

        $price->markAsDeleted();
        $priceRepository->save($price);

        return new JsonResponse([], JsonResponse::HTTP_ACCEPTED);
    }
```

**Edit:**

```php
 #[IsGranted('ROLE_LEAD')]
    #[Route('/app/product/{id}/update', name: 'app_product_update_view', methods: ['GET'])]
    public function viewUpdateProduct(
        Request $request,
        ProductRepositoryInterface $productRepository,
        ProductDataMapper $dataMapper,
        TaxTypeRepositoryInterface $taxTypeRepository,
        TaxTypeDataMapper $taxTypeDataMapper,
        SerializerInterface $serializer,
    ): Response {
        $this->getLogger()->info('Received request to read update products', ['product_id' => $request->get('id')]);

        try {
            $product = $productRepository->getById($request->get('id'));
        } catch (NoEntityFoundException $exception) {
            return new JsonResponse(['success' => false], JsonResponse::HTTP_NOT_FOUND);
        }

        $taxTypes = $taxTypeRepository->getAll();
        $taxTypeDtos = array_map([$taxTypeDataMapper, 'createAppDto'], $taxTypes);
        $view = new UpdateProductView();
        $view->setProduct($dataMapper->createAppDtoFromProduct($product));
        $view->setTaxTypes($taxTypeDtos);

        $json = $serializer->serialize($view, 'json');

        return new JsonResponse($json, json: true);
    }

    #[IsGranted('ROLE_LEAD')]
    #[Route('/app/product/{id}', name: 'app_product_update', methods: ['POST'])]
    public function updateProduct(
        Request $request,
        ProductRepositoryInterface $productRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ProductDataMapper $productFactory,
    ): Response {
        $this->getLogger()->info('Received request to write update products', ['product_id' => $request->get('id')]);

        try {
            /** @var Product $product */
            $product = $productRepository->getById($request->get('id'));
        } catch (NoEntityFoundException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }

        /** @var CreateProduct $dto */
        $dto = $serializer->deserialize($request->getContent(), CreateProduct::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $errorOutput = [];
            foreach ($errors as $error) {
                $propertyPath = $error->getPropertyPath();
                $errorOutput[$propertyPath] = $error->getMessage();
            }

            return new JsonResponse([
                'errors' => $errorOutput,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $newProduct = $productFactory->createFromAppCreate($dto, $product);

        $productRepository->save($newProduct);
        $dto = $productFactory->createAppDtoFromProduct($newProduct);
        $jsonResponse = $serializer->serialize($dto, 'json');

        return new JsonResponse($jsonResponse, JsonResponse::HTTP_ACCEPTED, json: true);
    }
```

### Frontend

* Within the Frontend, submit buttons should use the Parthenon SubmitButton component.
* When loading pages or changing views, it should use LoadingScreen component.
* THERE SHOULD NEVER BE RAW STRINGS IN THE TEMPLATE. EVERYTHING *MUST* BE A LOCALISATION ID
* Translations should be in British English, American English, and German
* CSS should use tailwind utils

## Comments

* All classes and methods should only have doc blocks if not type hinted.
* Inline comments should only be used for very complex logic. Almost never.

## Committing

* To ensure that the code style is correct YOU MUST run `web/vendor/bin/php-cs-fixer fix --allow-unsupported-php-version=yes` before committing any PHP changes.

## Development Workflow

### Creating New Tests

#### JavaScript Test Example
```javascript
// assets/services/__tests__/myservice.test.js
describe('MyService', () => {
    test('should perform expected operation', () => {
        // Test implementation
        expect(result).toBe(expected);
    });
});
```

#### PHP Test Example
```php
<?php
// tests/Unit/MyServiceTest.php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class MyServiceTest extends TestCase
{
    public function testShouldPerformExpectedOperation(): void
    {
        // Test implementation
        $this->assertEquals($expected, $actual);
    }
}
```

### Testing with Enums, DTOs, and Readonly Classes

1. **Enums**: When testing with PHP enums, always use the actual enum cases directly instead of mocking them:
    - ✅ DO: `$agent->method('getStatus')->willReturn(AgentStatus::Enabled);`
    - ❌ DON'T:
      ```php
      $status = $this->createMock(AgentStatus::class);
      $status->value = 'enabled';
      $agent->method('getStatus')->willReturn($status);
      ```

   When making assertions involving enums, always compare against the enum case directly rather than its string or numeric value:
    - ✅ DO: `$this->assertEquals(AgentStatus::Enabled, $agent->getStatus());`
    - ✅ DO: `$this->assertSame(AgentStatus::Enabled, $agent->getStatus());`
    - ❌ DON'T: `$this->assertEquals('enabled', $agent->getStatus()->value);`
   - ❌ DON'T: `$this->assertEquals(1, $agent->getStatus()->value);`

2. **DTOs**: When testing with DTOs (Data Transfer Objects), use the actual DTO classes rather than mocks:
    - ✅ DO: `$dto = new SomeResponseDto('value1', 'value2');`
    - ❌ DON'T: `$dto = $this->createMock(SomeResponseDto::class);`

3. **Readonly Classes**: Similar to DTOs, readonly classes should be instantiated directly in tests, not mocked:
    - ✅ DO: `$valueObject = new SomeValueObject('value1', 'value2');`
    - ❌ DON'T: `$valueObject = $this->createMock(SomeValueObject::class);`

Using real objects instead of mocks for these types provides several benefits:
- Tests more closely match real application behavior
- Eliminates subtle bugs caused by incomplete mocking
- Improves readability and maintainability of test code
- Reduces test fragility when refactoring these objects

Exception: Only mock these objects when absolutely necessary for specific test isolation requirements, and document the reason in a comment.


### Common PHPUnit Assertions
- `$this->assertTrue($condition)`
- `$this->assertEquals($expected, $actual)`
- `$this->assertStringContainsString($needle, $haystack)`
- `$this->assertCount($expectedCount, $array)`
- `$this->assertInstanceOf($expected, $actual)`

### Common Jest Matchers
- `expect(actual).toBe(expected)`
- `expect(actual).toEqual(expected)`
- `expect(string).toContain(substring)`
- `expect(array).toHaveLength(number)`
- `expect(promise).resolves.toBe(expected)`

## Key Dependencies

### Backend (PHP)
- Symfony 7.2 (Framework Bundle, Console, Mailer)
- Doctrine ORM with Migrations
- Parthenon (SaaS framework)
- JIRA Cloud REST API integration
- Monolog for logging

### Frontend (JavaScript)
- Vue.js 3 with Vue Router and Vuex
- Tailwind CSS with forms plugin
- FontAwesome icons
- Axios for HTTP requests
- Vue Stripe integration

### Development Tools
- Webpack Encore for asset compilation
- Babel for JavaScript transpilation
- Sass/SCSS support
- Jest for JavaScript testing
- PHPUnit for PHP testing
- Behat for BDD testing

## Debugging

### Symfony Profiler
Available in development mode at `/_profiler` after making requests.

### Log Files
- Application logs: `var/log/dev.log`
- Test logs: Check test environment logs

### Asset Issues
- Clear Webpack cache: `rm -rf node_modules/.cache`
- Rebuild assets: `npm run dev`
- Check for syntax errors in `webpack.config.js`

## Environment Configuration

### Environment Files
- `.env`: Main environment configuration
- `.env.local`: Local overrides (not committed)
- `.env.test`: Test environment settings

### Important Environment Variables
- `APP_ENV`: Application environment (dev/prod/test)
- `DATABASE_URL`: Database connection string
- `JIRA_*`: JIRA integration settings

---
### General Code Practices with Enums

1. **Enum Comparisons in Source Code**: When comparing or asserting enum values in source code (not just tests), always compare against the enum case directly:
    - ✅ DO: `if ($status === AgentStatus::Enabled) { ... }`
    - ❌ DON'T: `if ($status->value === 'enabled') { ... }`
    - ✅ DO: `return $status === AgentStatus::Disabled;`
    - ❌ DON'T: `return $status->value === 0;`

2. **Using Enums in Match Expressions**: Prefer using match expressions with enum cases:
    - ✅ DO:
      ```php
      $result = match($status) {
          AgentStatus::Enabled => 'active',
          AgentStatus::Disabled => 'inactive',
          default => 'unknown'
      };
      ```
    - ❌ DON'T:
      ```php
      $result = match($status->value) {
          'enabled' => 'active',
          'disabled' => 'inactive',
          default => 'unknown'
      };
      ```

Using enum cases directly rather than their values provides type safety, better refactoring support, and clearer code intent. It also prevents issues if the string or numeric representation of an enum changes.

*Last updated: 2025-08-27*
