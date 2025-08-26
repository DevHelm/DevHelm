#!/bin/bash

# Update namespace in PHP files from App\ to DevHelm\Control\
find web/src -type f -name "*.php" -exec sed -i 's/namespace App\\/namespace DevHelm\\Control\\/g' {} \;

# Update use statements in PHP files from App\ to DevHelm\Control\
find web/src -type f -name "*.php" -exec sed -i 's/use App\\/use DevHelm\\Control\\/g' {} \;

# Update namespace in test files from App\Tests\ to Test\DevHelm\Control\
find web/tests -type f -name "*.php" -exec sed -i 's/namespace App\\Tests\\/namespace Test\\DevHelm\\Control\\/g' {} \;

# Update use statements in test files from App\Tests\ to Test\DevHelm\Control\
find web/tests -type f -name "*.php" -exec sed -i 's/use App\\Tests\\/use Test\\DevHelm\\Control\\/g' {} \;

# Update use statements in test files for source code from App\ to DevHelm\Control\
find web/tests -type f -name "*.php" -exec sed -i 's/use App\\/use DevHelm\\Control\\/g' {} \;

echo "Namespace updates completed."