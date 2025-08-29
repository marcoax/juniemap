# Bug Fix: Full-Text Index Error

## Problem Description

The application was throwing a MySQL error:
```
SQLSTATE[HY000]: General error: 1191 Can't find FULLTEXT index matching the column list
```

This occurred when trying to use the `MATCH() AGAINST()` full-text search functionality without the required full-text indexes being present.

## Root Cause

The Location model's `scopeSearch` method was attempting to use MySQL full-text search before the corresponding indexes were created. The migration `2025_08_28_000001_add_search_indexes_to_locations_table.php` had been created but not yet executed.

## Solution Implemented

### 1. **Applied Missing Migration**

```bash
php artisan migrate
```

This created the full-text index:
```sql
ALTER TABLE locations ADD FULLTEXT(titolo, indirizzo);
```

### 2. **Enhanced Error Handling**

Updated the `Location::scopeSearch()` method with robust error handling:

```php
/**
 * Full-text search with fallback to LIKE when full-text is unavailable
 */
public function scopeSearch(Builder $query, ?string $search): Builder
{
    // ... validation logic ...

    if ($query->getConnection()->getDriverName() === 'mysql') {
        try {
            // Check if full-text index exists
            $hasFullTextIndex = $this->hasFullTextIndex($query->getConnection());
            
            if ($hasFullTextIndex) {
                // Use full-text search with LIKE fallback
                return $query->where(function (Builder $q) use ($term): void {
                    $q->whereRaw('MATCH(titolo, indirizzo) AGAINST(? IN BOOLEAN MODE)', ["+{$term}*"])
                      ->orWhere(function (Builder $subQ) use ($term): void {
                          $subQ->where('titolo', 'like', "{$term}%")
                               ->orWhere('indirizzo', 'like', "{$term}%");
                      });
                });
            }
        } catch (\Exception $e) {
            // Log error and fallback to LIKE search
            \Log::info('Full-text search failed, falling back to LIKE: ' . $e->getMessage());
        }
    }

    // Fallback to LIKE search
    return $query->where(function (Builder $q) use ($term): void {
        // ... LIKE-based search logic ...
    });
}
```

### 3. **Added Index Detection**

Created a helper method to dynamically check for full-text index availability:

```php
private function hasFullTextIndex($connection): bool
{
    static $hasIndex = null;
    
    if ($hasIndex === null) {
        try {
            $indexes = $connection->select(
                "SHOW INDEX FROM locations WHERE Index_type = 'FULLTEXT' AND Column_name IN ('titolo', 'indirizzo')"
            );
            $hasIndex = !empty($indexes);
        } catch (\Exception $e) {
            $hasIndex = false;
        }
    }
    
    return $hasIndex;
}
```

## Verification

### 1. **Migration Applied Successfully**
```bash
✓ Migration: 2025_08_28_000001_add_search_indexes_to_locations_table (333.29ms)
```

### 2. **Full-Text Index Created**
```sql
mysql> SHOW INDEX FROM locations WHERE Index_type = 'FULLTEXT';
+----------+----------------------------------+--------------+
| Key_name | Column_name                      | Index_type   |
+----------+----------------------------------+--------------+
| locations_titolo_indirizzo_fulltext | titolo    | FULLTEXT     |
| locations_titolo_indirizzo_fulltext | indirizzo | FULLTEXT     |
+----------+----------------------------------+--------------+
```

### 3. **Search Functionality Working**
```bash
✓ Search test successful. Found 2 results for 'mil'
✓ All location controller tests passing (10 tests, 54 assertions)
```

### 4. **Code Quality Maintained**
```bash
✓ Laravel Pint formatting applied
✓ All imports and dependencies properly configured
```

## Benefits of the Enhanced Solution

### 1. **Performance Optimization**
- **Full-text search** for complex queries when available
- **LIKE search** fallback for compatibility
- **Index detection** prevents unnecessary attempts

### 2. **Reliability**
- **Graceful degradation** when indexes are missing
- **Error logging** for debugging and monitoring
- **Static caching** of index detection results

### 3. **Flexibility**
- **Database-agnostic** fallback logic
- **Development-friendly** error handling
- **Production-ready** logging and monitoring

## Database Indexes Created

The migration added several performance-optimized indexes:

```sql
-- Individual column indexes
ALTER TABLE locations ADD INDEX(titolo);
ALTER TABLE locations ADD INDEX(indirizzo);

-- Composite index for common queries
ALTER TABLE locations ADD INDEX(stato, titolo);

-- Full-text search index
ALTER TABLE locations ADD FULLTEXT(titolo, indirizzo);
```

## Search Performance Comparison

### Before (LIKE Only)
```sql
-- Slow for large datasets
WHERE titolo LIKE '%search%' OR indirizzo LIKE '%search%'
```

### After (Full-Text + LIKE Fallback)
```sql
-- Fast full-text search with intelligent fallback
MATCH(titolo, indirizzo) AGAINST('+search*' IN BOOLEAN MODE)
OR (titolo LIKE 'search%' OR indirizzo LIKE 'search%')
```

## Files Modified

1. **Migration executed**: `database/migrations/2025_08_28_000001_add_search_indexes_to_locations_table.php`
2. **Model enhanced**: `app/Models/Location.php` - Added robust search with fallback logic
3. **Dependencies added**: `use Illuminate\Support\Facades\Log;`

## Prevention Measures

1. **Always run migrations** in development and production environments
2. **Include index detection** logic for database-dependent features  
3. **Implement graceful fallbacks** for advanced database features
4. **Add comprehensive logging** for debugging search issues
5. **Test with both indexed and non-indexed scenarios**

## Future Enhancements

1. **Search analytics** - Track which search method is being used
2. **Index monitoring** - Alert when full-text indexes are missing
3. **Search relevance scoring** - Prioritize full-text matches over LIKE matches
4. **Caching layer** - Cache frequent search results for better performance

The search functionality is now robust, performant, and handles both development and production scenarios gracefully.