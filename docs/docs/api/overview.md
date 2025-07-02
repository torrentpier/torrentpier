---
sidebar_position: 1
title: Overview
---

# API Overview

Welcome to the API documentation. This section covers all available REST API endpoints for interacting with the application programmatically.

## Base URL

All API endpoints are prefixed with `/api/` and use the following base URL:

```http
https://your-domain.com/api/
```

## Authentication

The API uses Laravel Sanctum for authentication. You can authenticate using:

### 1. Session-based Authentication (Web)
For web applications using the same domain, authentication is handled via Laravel's built-in session management.

### 2. Token-based Authentication (API)
For external applications or mobile apps, use API tokens:

```bash
# Get your user token
curl -X POST https://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
```

Include the token in the Authorization header:

```text
Authorization: Bearer your-token-here
```

## Response Format

All API responses follow a consistent JSON format:

### Success Response

```json
{
  "data": {
    "id": 1,
    "title": "Example"
  }
}
```

### Collection Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Example 1"
    },
    {
      "id": 2,
      "title": "Example 2"
    }
  ],
  "links": {
    "first": "https://api.example.com/emoji/emojis?page=1",
    "last": "https://api.example.com/emoji/emojis?page=10",
    "prev": null,
    "next": "https://api.example.com/emoji/emojis?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 500
  }
}
```

### Error Response

```json
{
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

## HTTP Status Codes

| Code | Description                                |
|------|--------------------------------------------|
| 200  | OK - Request successful                    |
| 201  | Created - Resource created successfully    |
| 204  | No Content - Resource deleted successfully |
| 400  | Bad Request - Invalid request data         |
| 401  | Unauthorized - Authentication required     |
| 403  | Forbidden - Insufficient permissions       |
| 404  | Not Found - Resource not found             |
| 422  | Unprocessable Entity - Validation failed   |
| 500  | Internal Server Error - Server error       |

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authenticated users**: 60 requests per minute
- **Unauthenticated users**: 20 requests per minute

Rate limit headers are included in responses:

```text
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

## Pagination

List endpoints support pagination using query parameters:

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 50, max: 100)

Example:

```http
GET /api/emoji/emojis?page=2&per_page=25
```

## Filtering and Searching

Many endpoints support filtering and searching:

### Query Parameters
- `search` - Text search across relevant fields
- `category_id` - Filter by category ID
- `emoji_id` - Filter by emoji ID (for aliases)

### Relationship Loading
Use these parameters to include related data:

- `with_category` - Include category information
- `with_emojis` - Include emoji list (for categories)
- `with_aliases` - Include alias list (for emojis)
- `with_emoji` - Include emoji information (for aliases)

Example:

```http
GET /api/emoji/emojis?search=smile&with_category=1&with_aliases=1
```

## Versioning

The current API version is v1. Future versions will be available at:

```http
/api/v2/endpoint
```

Breaking changes will always result in a new API version.
