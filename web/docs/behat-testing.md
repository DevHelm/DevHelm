# Running Behat Tests with Docker

This document explains how to run Behat tests for the DevHelm web application using Docker.

## Prerequisites

- Docker and Docker Compose installed on your system
- Docker daemon running

## Setup

1. Make sure you have the latest code from the repository
2. Ensure the `run-behat-tests.sh` script has execute permissions:
   ```bash
   chmod +x run-behat-tests.sh
   ```

## Running Behat Tests

### Using the Script

We've created a script to simplify running Behat tests with Docker. The script handles starting the Docker services and executing the tests in the correct container.

From the `web` directory, run:

```bash
./run-behat-tests.sh
```

This will:
1. Start all required Docker services if they're not already running
2. Execute all Behat tests in the Docker environment

### Script Options

The script supports several options:

```bash
# Display help information
./run-behat-tests.sh -h

# Run a specific feature file
./run-behat-tests.sh features/demo.feature

# Perform a dry run (syntax check without executing tests)
./run-behat-tests.sh -d

# Run with verbose output
./run-behat-tests.sh -v

# Combine options
./run-behat-tests.sh -v features/Agents/create.feature
```

### Manual Method

If you prefer to run the commands manually, you can use:

```bash
# Start Docker services
docker compose up -d

# Run all Behat tests
docker compose exec php-fpm vendor/bin/behat

# Run a specific feature
docker compose exec php-fpm vendor/bin/behat features/demo.feature

# Run with specific options
docker compose exec php-fpm vendor/bin/behat --dry-run
```

## Troubleshooting

### Docker Not Running

If you see an error message about Docker not running, start the Docker daemon first.

### Container Not Found

If you get an error about the container not being found, make sure:
1. You're in the `web` directory when running the commands
2. The Docker services are running (`docker compose ps`)
3. The service name is correct (`php-fpm`)

### Failed Tests

If tests fail:
1. Check the error messages in the output
2. Verify that your Docker environment is properly configured
3. Ensure all required services are running
4. Check that the database is properly initialized

## Writing New Behat Tests

When writing new Behat tests:

1. Create feature files in the `features/` directory
2. Use appropriate context classes from `App\Tests\Behat\`
3. Follow the Gherkin syntax for scenarios
4. Use Docker to run and verify your tests

For more information on writing Behat tests, refer to the [Behat documentation](https://docs.behat.org/).