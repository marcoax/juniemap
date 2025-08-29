# JunieMap API Documentation

## Overview

The JunieMap API provides endpoints for managing and retrieving location data with an interactive map interface. All endpoints return JSON responses and follow RESTful conventions.

## Base URL

```
https://your-domain.com
```

## Authentication

Currently, the API endpoints for location viewing are public. Authentication may be required for administrative operations in future versions.

## Content Type

All API endpoints accept and return JSON data:

```
Content-Type: application/json
```

## Error Handling

### Error Response Format

```json
{
  "message": "Human-readable error message",
  "error": "ERROR_CODE"
}
```

### HTTP Status Codes

- `200` - Success
- `400` - Bad Request (Invalid parameters)
- `404` - Not Found
- `422` - Unprocessable Entity (Validation errors)
- `500` - Internal Server Error

## Endpoints

### Map Interface

#### GET `/`

Returns the main map interface with initial location data.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search term for locations |
| `stato` | enum | No | Filter by location status (`attivo`, `disattivo`, `in_allarme`) |

**Response:**
Returns an Inertia.js response with the map page component and initial data.

---

### Location Search

#### GET `/locations/search`

Search and filter locations with caching support.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search term (min: 1 char, max: 255 chars) |
| `stato` | enum | No | Location status filter |

**Valid `stato` values:**
- `attivo` - Active locations
- `disattivo` - Inactive locations  
- `in_allarme` - Locations in alarm state

**Example Request:**
```bash
GET /locations/search?search=museo&stato=attivo
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "titolo": "Museo Nazionale",
      "indirizzo": "Via Roma 123, Milano",
      "latitude": 45.464664,
      "longitude": 9.188540,
      "stato": "attivo"
    }
  ]
}
```

**Error Responses:**

*Validation Error (422):*
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "stato": ["Lo stato selezionato non è valido."],
    "search": ["Il termine di ricerca deve contenere almeno 1 carattere."]
  }
}
```

---

### Location Details

#### GET `/locations/{id}`

Get basic location information by ID.

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Location ID |

**Example Request:**
```bash
GET /locations/123
```

**Response:**
```json
{
  "data": {
    "id": 123,
    "titolo": "Duomo di Milano",
    "descrizione": "Cattedrale gotica nel centro di Milano",
    "indirizzo": "Piazza del Duomo, Milano",
    "latitude": 45.464664,
    "longitude": 9.188540,
    "stato": {
      "value": "attivo",
      "label": "Attivo",
      "color": "#10B981",
      "css_class": "success"
    },
    "orari_apertura": "08:00-19:00",
    "prezzo_biglietto": "€8",
    "sito_web": "https://duomomilano.it",
    "telefono": "+39 02 7202 3375",
    "note_visitatori": "Prenotazione consigliata nei weekend",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-20T14:45:00.000000Z"
  }
}
```

**Error Response (404):**
```json
{
  "message": "Location not found",
  "error": "LOCATION_NOT_FOUND"
}
```

---

#### GET `/locations/{id}/details`

Get comprehensive location details with caching (optimized for info windows).

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Location ID |

**Example Request:**
```bash
GET /locations/123/details
```

**Response:**
Same as `/locations/{id}` but with enhanced caching for better performance.

**Error Response (404):**
```json
{
  "message": "Location details not found", 
"error": "LOCATION_NOT_FOUND"
}
```

## Data Models

### Location (List View)

Used in search results and map display:

```typescript
interface LocationBase {
  id: number
  titolo: string
  indirizzo: string  
  latitude: number
  longitude: number
  stato: 'attivo' | 'disattivo' | 'in_allarme'
}
```

### Location (Detail View)

Used in detailed views:

```typescript
interface LocationFull {
  id: number
  titolo: string
  descrizione: string | null
  indirizzo: string
  latitude: number
  longitude: number
  stato: {
    value: 'attivo' | 'disattivo' | 'in_allarme'
    label: string
    color: string
    css_class: string
  }
  orari_apertura: string | null
  prezzo_biglietto: string | null
  sito_web: string | null
  telefono: string | null
  note_visitatori: string | null
  created_at: string
  updated_at: string
}
```

### Location Status

Location status with associated styling:

```typescript
interface LocationStatoDetails {
  value: 'attivo' | 'disattivo' | 'in_allarme'
  label: 'Attivo' | 'Disattivo' | 'In Allarme'
  color: '#10B981' | '#9CA3AF' | '#EF4444'
  css_class: 'success' | 'muted' | 'danger'
}
```

## Caching

### Cache Headers

Search and detail endpoints include appropriate cache headers for client-side caching.

### Server-Side Caching

- Search results: Cached for 15 minutes
- Location details: Cached for 15 minutes
- Cache keys are generated based on search parameters

### Cache Invalidation

Caches are automatically invalidated when location data is updated through administrative interfaces.

## Rate Limiting

Currently, no rate limiting is implemented. For production deployments, consider implementing appropriate rate limiting based on your usage patterns.

## Examples

### Search Active Museums

```bash
curl -X GET "https://your-domain.com/locations/search?search=museo&stato=attivo" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest"
```

### Get Location Details for Map Info Window

```bash  
curl -X GET "https://your-domain.com/locations/123/details" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest"
```

### Search Locations Near Term

```bash
curl -X GET "https://your-domain.com/locations/search?search=centro" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest"
```

## JavaScript/TypeScript Integration

### Using with Fetch API

```javascript
// Search locations
const searchLocations = async (searchTerm, stato = null) => {
  const params = new URLSearchParams()
  if (searchTerm) params.set('search', searchTerm)
  if (stato) params.set('stato', stato)
  
  const response = await fetch(`/locations/search?${params}`, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  
  if (!response.ok) {
    throw new Error('Search failed')
  }
  
  return await response.json()
}

// Get location details
const getLocationDetails = async (locationId) => {
  const response = await fetch(`/locations/${locationId}/details`, {
    headers: {
      'Accept': 'application/json', 
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  
  if (!response.ok) {
    throw new Error('Failed to fetch location details')
  }
  
  return await response.json()
}
```

### Error Handling

```javascript
try {
  const results = await searchLocations('museo', 'attivo')
  console.log('Found locations:', results.data)
} catch (error) {
  if (error.response?.status === 422) {
    // Handle validation errors
    const validationErrors = await error.response.json()
    console.error('Validation errors:', validationErrors.errors)
  } else {
    console.error('Search error:', error.message)
  }
}
```