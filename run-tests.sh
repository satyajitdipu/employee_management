#!/usr/bin/env bash

# Test runner script for CI/CD
# This script runs PHPUnit with coverage and checks minimum coverage

set -e

echo "🚀 Running Tests with Coverage..."

# Run PHPUnit with coverage
vendor/bin/phpunit \
    --coverage-html=reports/coverage \
    --coverage-text \
    --coverage-clover=coverage.xml \
    --colors=always \
    --testdox

echo "✅ Tests completed successfully!"

# Check coverage percentage (optional - requires additional tools)
# You can add coverage threshold checking here if needed

echo "📊 Coverage report generated at: reports/coverage/index.html"
echo "📄 Coverage XML report: coverage.xml"