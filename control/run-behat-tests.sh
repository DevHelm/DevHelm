#!/bin/bash

# Script to run Behat tests using Docker

# Set script to exit on error
set -e

# Display help information
function show_help {
    echo "Behat Test Runner with Docker"
    echo ""
    echo "Usage: ./run-behat-tests.sh [options] [feature]"
    echo ""
    echo "Options:"
    echo "  -h, --help       Show this help message"
    echo "  -d, --dry-run    Perform a dry run (syntax check)"
    echo "  -v, --verbose    Run with verbose output"
    echo ""
    echo "Arguments:"
    echo "  feature          Path to specific feature file (optional)"
    echo ""
    echo "Examples:"
    echo "  ./run-behat-tests.sh                        # Run all tests"
    echo "  ./run-behat-tests.sh features/demo.feature  # Run specific feature"
    echo "  ./run-behat-tests.sh -d                     # Perform a dry run"
    echo "  ./run-behat-tests.sh -v                     # Run with verbose output"
    echo ""
}

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "Error: Docker is not running. Please start Docker and try again."
    exit 1
fi

# Parse arguments
DRY_RUN=false
VERBOSE=false
FEATURE=""

while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -d|--dry-run)
            DRY_RUN=true
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        *)
            FEATURE=$1
            shift
            ;;
    esac
done

echo "Starting Docker services..."
docker compose up -d

# Build the command
CMD="vendor/bin/behat"

if [ "$DRY_RUN" = true ]; then
    CMD="$CMD --dry-run"
fi

if [ "$VERBOSE" = true ]; then
    CMD="$CMD --verbose"
fi

if [ -n "$FEATURE" ]; then
    CMD="$CMD $FEATURE"
fi

echo "Running Behat tests with Docker..."
echo "Command: docker compose exec php-fpm $CMD"
echo ""

# Execute the command
docker compose exec php-fpm $CMD

echo ""
echo "Behat tests completed."