# Contributing to Atelier SVG

Thank you for your interest in contributing! We welcome bug reports, feature suggestions, and pull requests.

## Setup

```bash
git clone https://github.com/<your-username>/svg.git
cd svg
composer install
```

## Composer Scripts

| Command | Description |
|---------|-------------|
| `composer test` | Run tests (PHPUnit) |
| `composer sa` | Run static analysis (PHPStan, max level) |
| `composer cs` | Check code style (dry-run) |
| `composer cs:fix` | Fix code style |
| `composer rector` | Check Rector suggestions (dry-run) |
| `composer coverage` | Run tests with coverage report |
| `composer qa` | Run cs + sa + test |

## Development Workflow

1. Create a feature branch: `git checkout -b feature/my-feature`
2. Write your code:
   - `declare(strict_types=1);` in all files
   - `final` classes, `readonly` properties where possible
   - Typed constants (`const string`, `const int`, etc.)
3. Add tests for new features or bug fixes
4. Run all checks before submitting:
   ```bash
   composer qa
   ```

## Quality Standards

This project maintains strict quality standards:

- **PHPStan** at max level, zero errors
- **100% code coverage** on classes, methods, and lines
- **PHP-CS-Fixer** with project configuration
- **Rector** with project configuration

All checks must pass before merging.

## Pull Requests

- Descriptive title that briefly explains the change
- Context describing *why* the change is needed
- Checklist:
  - [ ] Tests pass (`composer test`)
  - [ ] Static analysis passes (`composer sa`)
  - [ ] Code style is clean (`composer cs`)
  - [ ] Coverage remains at 100% (`composer coverage`)

## License

By contributing, you agree that your code will be licensed under the MIT License.
