---
sidebar_position: 1
title: Overview
---

# API overview

TorrentPier provides a REST API for programmatic access to tracker functionality.

:::caution Work in progress
The API is under active development. Endpoints and response formats may change.
:::

## Base URL

All API endpoints are prefixed with `/api/`:

```
https://your-domain.com/api/
```

## Authentication

The API supports multiple authentication methods:

### Session-based authentication (web)

For web applications using the same domain, authentication is handled via built-in session management.

### Token-based authentication (API)

For external applications or scripts, use API tokens:

```bash
curl -X POST https://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"username": "user", "password": "password"}'
```

Include the token in subsequent requests:

```
Authorization: Bearer your-token-here
```

## Response format

All API responses follow a consistent JSON format:

### Success response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Example"
  }
}
```

### Collection response

```json
{
  "success": true,
  "data": [
    {"id": 1, "title": "Example 1"},
    {"id": 2, "title": "Example 2"}
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 500,
    "total_pages": 10
  }
}
```

### Error response

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "title": ["The title field is required."]
    }
  }
}
```

## HTTP status codes

| Code | Description |
|------|-------------|
| 200 | OK — request successful |
| 201 | Created — resource created successfully |
| 204 | No Content — resource deleted successfully |
| 400 | Bad Request — invalid request data |
| 401 | Unauthorized — authentication required |
| 403 | Forbidden — insufficient permissions |
| 404 | Not Found — resource not found |
| 422 | Unprocessable Entity — validation failed |
| 429 | Too Many Requests — rate limit exceeded |
| 500 | Internal Server Error — server error |

## Rate limiting

API requests are rate-limited to prevent abuse:

- **Authenticated users**: 60 requests per minute
- **Unauthenticated users**: 20 requests per minute

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

## Pagination

List endpoints support pagination using query parameters:

| Parameter | Default | Max | Description |
|-----------|---------|-----|-------------|
| `page` | 1 | — | Page number |
| `per_page` | 50 | 100 | Items per page |

Example:

```
GET /api/torrents?page=2&per_page=25
```

## Filtering and sorting

Many endpoints support filtering and sorting:

### Query parameters

- `search` — text search across relevant fields
- `sort` — field to sort by
- `order` — sort direction (`asc` or `desc`)

Example:

```
GET /api/torrents?search=linux&sort=created_at&order=desc
```

## Versioning

The current API version is **v1**. Future versions will be available at:

```
/api/v2/endpoint
```

Breaking changes will always result in a new API version.
