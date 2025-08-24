# ComControl Web Development Guidelines

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

### Testing Guidelines

1. **JavaScript Tests**: Place in `assets/services/__tests__/` with `.test.js` extension
2. **PHP Unit Tests**: Place in `tests/Unit/` with `Test.php` suffix
3. **Integration Tests**: Use `tests/Integration/` directory
4. **BDD Tests**: Create `.feature` files in `features/` directory

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

*Last updated: 2025-08-24*
