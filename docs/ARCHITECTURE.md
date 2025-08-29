# JunieMap Architecture Documentation

## Overview

JunieMap is a modern Laravel 12 application with React frontend that provides an interactive map interface for managing and viewing locations. The application follows Domain-Driven Design principles and uses the latest Laravel ecosystem technologies.

## Technology Stack

### Backend
- **Laravel 12.25.0** - Main framework
- **PHP 8.3.11** - Programming language with strict typing
- **Inertia.js 2.0.5** - Full-stack framework
- **MySQL** - Primary database
- **Laravel Pint 1.24.0** - Code formatting

### Frontend
- **React 19.0.0** - UI framework
- **TypeScript** - Type-safe JavaScript
- **Tailwind CSS 4.0.0** - Utility-first CSS framework
- **Vite** - Build tool and development server

## Architecture Patterns

### Domain Layer

#### Enums
- `LocationStato` - Type-safe enum for location statuses (attivo, disattivo, in_allarme)
  - Provides helper methods for colors, labels, and validation
  - Ensures data consistency across the application

#### Data Transfer Objects (DTOs)
- `LocationSearchDto` - Encapsulates location search parameters
  - Provides type safety for search operations
  - Handles cache key generation
  - Validates and sanitizes input data

#### Services
- `LocationService` - Core business logic for location operations
  - Handles caching strategies
  - Coordinates between repositories and controllers
  - Provides clean API for location operations

#### Constants
- `CacheKeys` - Centralized cache key management
  - Consistent cache key generation
  - Configurable TTL values
  - Type-safe cache key patterns

### Application Layer

#### Controllers
- `LocationController` - HTTP request handling
  - Uses dependency injection for services
  - Returns structured API responses
  - Handles error scenarios gracefully

#### Resources
- `LocationResource` - Full location data transformation for API responses
- `LocationListResource` - Optimized location data for list/map views

#### Requests
- `LocationSearchRequest` - Validates and sanitizes search input
  - Uses enum validation for stato field
  - Provides DTO conversion
  - Custom validation messages

#### Exceptions
- `LocationNotFoundException` - Custom exception for missing locations
- `InvalidLocationSearchException` - Custom exception for invalid search parameters

### Infrastructure Layer

#### Models
- `Location` - Eloquent model with enhanced capabilities
  - Uses enum casting for stato field
  - Optimized query scopes for search and filtering
  - Geospatial query support (nearby locations)
  - Full-text search optimization

#### Migrations
- Database schema with proper indexing
- Geospatial coordinate precision
- Search-optimized indexes

## Caching Strategy

### Cache Keys
- **Location Details**: `locations.details:{id}`
- **Location Search**: `locations.search:{hash}`
- **Map Data**: `locations.all_for_map`

### Cache TTL
- Search results: 15 minutes
- Location details: 15 minutes  
- Map data: 1 hour

### Cache Invalidation
- Location-specific cache clearing on updates
- Search cache clearing strategies
- Map cache invalidation on data changes

## Database Design

### Indexes
- Primary key on `id`
- Unique index on `titolo`
- Index on `stato` for filtering
- Composite index on `[latitude, longitude]` for geospatial queries
- Composite index on `[stato, titolo]` for common queries
- Full-text index on `[titolo, indirizzo]` for search

### Data Types
- `latitude`: `decimal(10,8)` for precision
- `longitude`: `decimal(11,8)` for precision
- `stato`: `enum` with predefined values

## API Design

### Endpoints

#### GET `/`
- Returns the main map interface
- Includes initial location data
- Handles search filters from query parameters

#### GET `/locations/search`
- Cached search endpoint
- Supports text search and stato filtering
- Returns optimized data for map/list display

#### GET `/locations/{id}/details`
- Returns full location information
- Cached for performance
- Used for detailed views and map info windows

### Response Format

#### Success Response
```json
{
  "data": {
    "id": 1,
    "titolo": "Location Name",
    "stato": {
      "value": "attivo",
      "label": "Attivo", 
      "color": "#10B981",
      "css_class": "success"
    }
  }
}
```

#### Error Response
```json
{
  "message": "Location not found",
  "error": "LOCATION_NOT_FOUND"
}
```

## Frontend Architecture

### Type System
- Comprehensive TypeScript interfaces
- Type-safe API communication
- Enum-like union types for consistency

### Custom Hooks
- `useLocationSearch` - Debounced search with error handling
- `useLocationDetails` - Location detail fetching with caching
- State management with proper cleanup

### Components
- Modular, reusable React components
- Proper prop typing with TypeScript interfaces
- Consistent styling with Tailwind CSS

### API Client
- Centralized API communication
- Custom error classes
- Automatic request/response transformation

## Performance Optimizations

### Database
- Strategic indexing for common queries
- Full-text search for MySQL
- Efficient geospatial queries
- Query result caching

### Frontend
- Debounced search requests
- Component memoization where appropriate
- Efficient re-rendering strategies
- Lazy loading for heavy components

### Caching
- Multi-level caching strategy
- Cache invalidation on updates
- Optimized cache key generation

## Security Considerations

### Input Validation
- Server-side validation with Form Requests
- Client-side TypeScript typing
- SQL injection prevention through Eloquent
- XSS prevention through proper escaping

### Error Handling
- Custom exceptions with proper HTTP codes
- No sensitive information in error messages
- Graceful degradation for API failures

## Testing Strategy

### Unit Tests
- Enum functionality
- DTO operations
- Service layer logic
- Utility functions

### Feature Tests
- API endpoint behavior
- Resource transformations
- Controller integration
- Database interactions

### Test Coverage
- Comprehensive test suite for new components
- Edge case coverage
- Error scenario testing

## Deployment Considerations

### Database Migrations
- Proper index creation for production
- Data migration strategies
- Rollback procedures

### Caching
- Production cache driver configuration
- Cache warming strategies
- Monitoring and alerting

### Performance Monitoring
- Query performance tracking
- Cache hit/miss ratios
- Error rate monitoring