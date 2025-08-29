# JunieMap Development Guide

## Getting Started

### Prerequisites

- PHP 8.3+
- Node.js 18+ 
- Composer
- MySQL 8.0+
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/juniemap.git
   cd juniemap
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database configuration**
   
   Update your `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=juniemap
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Google Maps API (Optional)**
   
   Add your Google Maps API key to `.env`:
   ```env
   VITE_GOOGLE_MAPS_API_KEY=your_api_key_here
   ```

7. **Run migrations**
   ```bash
   php artisan migrate
   ```

8. **Seed sample data**
   ```bash
   php artisan db:seed
   ```

## Development Workflow

### Starting Development Server

**Backend (Laravel)**
```bash
php artisan serve
```

**Frontend (Vite)**
```bash
npm run dev
```

**Combined Development**
```bash
composer run dev
```

### Code Quality Tools

**Format PHP Code**
```bash
vendor/bin/pint
```

**Check PHP Code Style**
```bash
vendor/bin/pint --test
```

**TypeScript Checking**
```bash
npm run type-check
```

**Build for Production**
```bash
npm run build
```

## Project Structure

```
juniemap/
├── app/
│   ├── Constants/          # Application constants
│   ├── DataTransferObjects/ # DTOs for data transfer
│   ├── Enums/              # PHP enums
│   ├── Exceptions/         # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/    # HTTP controllers
│   │   ├── Requests/       # Form request validation
│   │   └── Resources/      # API resources
│   ├── Models/             # Eloquent models
│   └── Services/           # Business logic services
├── database/
│   ├── factories/          # Model factories
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── docs/                   # Documentation
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/                # Frontend JavaScript/TypeScript
│   │   ├── components/    # React components
│   │   ├── hooks/         # Custom React hooks
│   │   ├── pages/         # Inertia.js pages
│   │   ├── types/         # TypeScript type definitions
│   │   └── utils/         # Utility functions
│   └── views/             # Blade templates
└── tests/
    ├── Feature/           # Feature tests
    └── Unit/             # Unit tests
```

## Coding Standards

### PHP Standards

#### Type Declarations
- Always use strict typing: `declare(strict_types=1);`
- Use PHP 8.3 features (constructor property promotion, enums, etc.)
- Always provide return type declarations

**Example:**
```php
<?php

declare(strict_types=1);

namespace App\Services;

final readonly class LocationService
{
    public function __construct(
        private CacheRepository $cache,
    ) {}

    public function search(LocationSearchDto $dto): Collection
    {
        // Implementation
    }
}
```

#### Error Handling
- Use custom exceptions for domain errors
- Provide meaningful error messages
- Use proper HTTP status codes

#### Documentation
- Use PHPDoc blocks for complex methods
- Document array shapes and generic types
- Include `@throws` annotations

### TypeScript Standards

#### Type Safety
- Use interfaces for object shapes
- Prefer union types over enums for simple values
- Always type function parameters and return values

**Example:**
```typescript
interface LocationSearchParams {
  search?: string
  stato?: LocationStato
}

export async function searchLocations(
  params: LocationSearchParams
): Promise<LocationBase[]> {
  // Implementation
}
```

#### Error Handling
- Use custom error classes
- Provide type-safe error handling
- Handle both client and server errors

### Database Standards

#### Migrations
- Use proper column types for data precision
- Add indexes for frequently queried columns
- Include rollback methods

**Example:**
```php
public function up(): void
{
    Schema::table('locations', function (Blueprint $table): void {
        $table->index(['stato', 'titolo']);
        $table->fullText(['titolo', 'indirizzo']);
    });
}
```

#### Models
- Use enum casting for categorical data
- Provide query scopes for common filters
- Include proper relationships

### Testing Standards

#### Unit Tests
- Test individual class methods
- Use dependency injection for mocking
- Follow Arrange-Act-Assert pattern

#### Feature Tests
- Test complete workflows
- Use database transactions
- Test both success and failure cases

**Example:**
```php
public function test_search_returns_filtered_results(): void
{
    // Arrange
    $location = Location::factory()->create([
        'titolo' => 'Test Location',
        'stato' => LocationStato::Attivo->value,
    ]);

    // Act  
    $response = $this->getJson('/locations/search?search=Test');

    // Assert
    $response->assertOk();
    $response->assertJsonCount(1, 'data');
}
```

## Development Guidelines

### Creating New Features

1. **Start with Tests**
   - Write failing tests first
   - Cover both success and error cases
   - Use factories for test data

2. **Domain Layer First**
   - Create necessary enums, DTOs, and exceptions
   - Implement service layer logic
   - Add proper type declarations

3. **Application Layer**
   - Create controllers and requests
   - Add API resources for responses
   - Handle errors appropriately

4. **Frontend Integration**
   - Define TypeScript types
   - Create custom hooks if needed
   - Add components with proper typing

### Performance Considerations

#### Backend
- Use database indexes appropriately
- Implement caching for expensive operations
- Use query optimization techniques
- Avoid N+1 queries

#### Frontend  
- Debounce user input
- Implement proper loading states
- Use React.memo for expensive components
- Optimize bundle size

### Security Guidelines

#### Input Validation
- Always validate on the server side
- Use Laravel's validation rules
- Sanitize input data properly

#### Output Escaping
- Escape HTML content
- Use CSP headers
- Validate API responses

#### Error Handling
- Don't expose sensitive information
- Log errors appropriately
- Use consistent error formats

## Debugging

### Laravel Debugging

**Enable Debug Mode**
```env
APP_DEBUG=true
```

**View Logs**
```bash
tail -f storage/logs/laravel.log
```

**Database Query Logging**
```php
DB::enableQueryLog();
// Your code here
dd(DB::getQueryLog());
```

### Frontend Debugging

**Browser Console**
- Check network requests
- Monitor React DevTools
- Use TypeScript compiler errors

**Vite Development Server**
- Hot module replacement
- Source maps for debugging
- Error overlay

## Common Tasks

### Adding New Locations

**Via Tinker**
```bash
php artisan tinker
```

```php
Location::factory()->create([
    'titolo' => 'New Location',
    'stato' => LocationStato::Attivo->value,
]);
```

**Via Database Seeder**
```php
Location::factory()->count(10)->create();
```

### Clearing Caches

**Application Cache**
```bash
php artisan cache:clear
```

**Configuration Cache**
```bash
php artisan config:clear
```

**View Cache**
```bash
php artisan view:clear
```

**Route Cache**
```bash
php artisan route:clear
```

### Running Tests

**All Tests**
```bash
php artisan test
```

**Specific Test Class**
```bash
php artisan test tests/Feature/LocationControllerTest.php
```

**With Coverage** (requires Xdebug)
```bash
php artisan test --coverage
```

### Building for Production

**Optimize Autoloader**
```bash
composer install --optimize-autoloader --no-dev
```

**Cache Configuration**
```bash
php artisan config:cache
```

**Cache Routes**
```bash
php artisan route:cache
```

**Build Assets**
```bash
npm run build
```

## Troubleshooting

### Common Issues

**Database Connection Issues**
- Check `.env` database credentials
- Ensure MySQL is running
- Verify database exists

**NPM/Node Issues**
- Clear node_modules: `rm -rf node_modules && npm install`
- Check Node.js version compatibility
- Update package-lock.json

**Permission Issues**
```bash
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

**Missing Google Maps**
- Add `VITE_GOOGLE_MAPS_API_KEY` to `.env`
- Rebuild frontend: `npm run build`

### Getting Help

1. Check Laravel documentation
2. Review error logs
3. Use Laravel Debugbar for profiling
4. Check GitHub issues
5. Contact the development team

## Contribution Guidelines

### Pull Request Process

1. Create feature branch from `main`
2. Write tests for new functionality
3. Ensure all tests pass
4. Run code formatting tools
5. Update documentation if needed
6. Submit pull request with clear description

### Code Review Checklist

- [ ] Tests are included and passing
- [ ] Code follows project standards
- [ ] Documentation is updated
- [ ] No security vulnerabilities introduced
- [ ] Performance impact considered
- [ ] Error handling is appropriate

### Git Workflow

```bash
# Create feature branch
git checkout -b feature/new-location-feature

# Make changes and commit
git add .
git commit -m "feat: add location filtering by category"

# Push and create PR
git push origin feature/new-location-feature
```

Use conventional commit messages:
- `feat:` for new features
- `fix:` for bug fixes  
- `docs:` for documentation
- `style:` for formatting
- `refactor:` for code refactoring
- `test:` for tests
- `chore:` for maintenance