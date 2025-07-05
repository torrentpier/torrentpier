# Word Filters API

The Word Filters API allows you to manage content filtering rules for your forum. Word filters can automatically replace, block, or moderate content based on configurable patterns.

## Overview

Word filters support three main actions:
- **Replace**: Automatically replace matched patterns with specified text
- **Block**: Prevent content containing matched patterns from being posted
- **Moderate**: Flag content containing matched patterns for review

Pattern matching supports:
- **Exact**: Match exact words or phrases
- **Wildcard**: Match patterns with wildcards (*, ?)
- **Regex**: Match using regular expressions

## Endpoints

### List Word Filters

Retrieve a paginated list of word filters with optional filtering.

```text
GET /api/word-filters
```

#### Query Parameters

| Parameter      | Type    | Description                                          |
|----------------|---------|------------------------------------------------------|
| `filter_type`  | string  | Filter by type: `replace`, `block`, `moderate`       |
| `pattern_type` | string  | Filter by pattern type: `exact`, `wildcard`, `regex` |
| `severity`     | string  | Filter by severity: `low`, `medium`, `high`          |
| `is_active`    | boolean | Filter by active status                              |
| `applies_to`   | string  | Filter by content type                               |
| `search`       | string  | Search in pattern and notes fields                   |
| `per_page`     | integer | Number of items per page (max 100)                   |
| `page`         | integer | Page number                                          |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/word-filters?filter_type=replace&is_active=true&per_page=20" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": [
    {
      "id": 1,
      "pattern": "badword",
      "replacement": "******",
      "filter_type": "replace",
      "pattern_type": "exact",
      "severity": "high",
      "is_active": true,
      "case_sensitive": false,
      "applies_to": ["posts", "private_messages"],
      "notes": "Common profanity",
      "creator": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "links": {
    "first": "https://api.example.com/api/word-filters?page=1",
    "last": "https://api.example.com/api/word-filters?page=5",
    "prev": null,
    "next": "https://api.example.com/api/word-filters?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 20,
    "to": 20,
    "total": 100
  }
}
```

### Search Word Filters

Search word filters using full-text search.

```text
GET /api/word-filters/search
```

#### Query Parameters

| Parameter      | Type    | Required | Description                           |
|----------------|---------|----------|---------------------------------------|
| `q`            | string  | Yes      | Search query                          |
| `limit`        | integer | No       | Maximum results (max 100, default 20) |
| `filter_type`  | string  | No       | Filter by type                        |
| `pattern_type` | string  | No       | Filter by pattern type                |
| `severity`     | string  | No       | Filter by severity                    |
| `is_active`    | boolean | No       | Filter by active status               |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/word-filters/search?q=profanity&limit=10&filter_type=replace" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": [
    {
      "id": 1,
      "pattern": "profanity",
      "replacement": "****",
      "filter_type": "replace",
      "pattern_type": "wildcard",
      "severity": "medium",
      "is_active": true,
      "case_sensitive": false,
      "applies_to": ["posts", "comments"],
      "notes": "Wildcard profanity filter",
      "creator": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Get Word Filter

Retrieve a specific word filter by ID.

```text
GET /api/word-filters/{id}
```

#### Example Request

```bash
curl -X GET "https://api.example.com/api/word-filters/1" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": {
    "id": 1,
    "pattern": "badword",
    "replacement": "******",
    "filter_type": "replace",
    "pattern_type": "exact",
    "severity": "high",
    "is_active": true,
    "case_sensitive": false,
    "applies_to": ["posts", "private_messages"],
    "notes": "Common profanity",
    "creator": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    },
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

### Create Word Filter

Create a new word filter rule.

```text
POST /api/word-filters
```

#### Request Body

| Field            | Type    | Required    | Description                                           |
|------------------|---------|-------------|-------------------------------------------------------|
| `pattern`        | string  | Yes         | Pattern to match (max 255 chars)                      |
| `replacement`    | string  | Conditional | Required when filter_type is 'replace'                |
| `filter_type`    | string  | Yes         | Type: 'replace', 'block', 'moderate'                  |
| `pattern_type`   | string  | Yes         | Pattern type: 'exact', 'wildcard', 'regex'            |
| `severity`       | string  | No          | Severity: 'low', 'medium', 'high' (default: 'medium') |
| `is_active`      | boolean | No          | Active status (default: true)                         |
| `case_sensitive` | boolean | No          | Case sensitivity (default: false)                     |
| `applies_to`     | array   | Yes         | Content types to apply filter to                      |
| `notes`          | string  | No          | Optional notes (max 1000 chars)                       |

#### Example Request

```bash
curl -X POST "https://api.example.com/api/word-filters" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "pattern": "badword",
    "replacement": "******",
    "filter_type": "replace",
    "pattern_type": "exact",
    "severity": "high",
    "is_active": true,
    "case_sensitive": false,
    "applies_to": ["posts", "private_messages"],
    "notes": "Common profanity filter"
  }'
```

#### Example Response

```json
{
  "data": {
    "id": 25,
    "pattern": "badword",
    "replacement": "******",
    "filter_type": "replace",
    "pattern_type": "exact",
    "severity": "high",
    "is_active": true,
    "case_sensitive": false,
    "applies_to": ["posts", "private_messages"],
    "notes": "Common profanity filter",
    "creator": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    },
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

### Update Word Filter

Update an existing word filter. Supports partial updates.

```text
PATCH /api/word-filters/{id}
```

#### Request Body

Same fields as create, but all are optional except when changing filter_type to 'replace' (then replacement becomes required).

#### Example Request

```bash
curl -X PATCH "https://api.example.com/api/word-filters/25" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "severity": "medium",
    "is_active": false,
    "notes": "Updated profanity filter - temporarily disabled"
  }'
```

#### Example Response

```json
{
  "data": {
    "id": 25,
    "pattern": "badword",
    "replacement": "******",
    "filter_type": "replace",
    "pattern_type": "exact",
    "severity": "medium",
    "is_active": false,
    "case_sensitive": false,
    "applies_to": ["posts", "private_messages"],
    "notes": "Updated profanity filter - temporarily disabled",
    "creator": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    },
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:30:00.000000Z"
  }
}
```

### Delete Word Filter

Delete a word filter permanently.

```text
DELETE /api/word-filters/{id}
```

#### Example Request

```bash
curl -X DELETE "https://api.example.com/api/word-filters/25" \
  -H "Accept: application/json"
```

#### Example Response

```text
HTTP/1.1 204 No Content
```

## Error Responses

### 422 Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "pattern": [
      "The pattern field is required."
    ],
    "replacement": [
      "The replacement field is required when filter type is replace."
    ],
    "pattern_type": [
      "The selected pattern type is invalid."
    ]
  }
}
```

### 400 Search Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "q": ["The search query is required."],
    "limit": ["The limit must not be greater than 100."]
  }
}
```

### 404 Not Found

```json
{
  "message": "Word filter not found."
}
```

## Validation Rules

- **pattern**: Maximum 255 characters. Must be valid regex if pattern_type is `regex`
- **replacement**: Maximum 255 characters. Required when filter_type is `replace`
- **applies_to**: Must contain at least one valid content type
- **severity**: Must be one of: `low`, `medium`, `high`
- **filter_type**: Must be one of: `replace`, `block`, `moderate`
- **pattern_type**: Must be one of: `exact`, `wildcard`, `regex`

## Content Types

Available content types for `applies_to` field:
- `posts` - Forum posts
- `private_messages` - Private messages
- `comments` - Comments on posts
- `signatures` - User signatures
- `usernames` - Usernames
- `topics` - Topic titles

## Features

- Full-text search using Laravel Scout
- Flexible pattern matching (exact, wildcard, regex)
- Content type targeting
- Severity levels for prioritization
- User tracking for filter creation
- Comprehensive validation
- Pagination support
- Active/inactive status management

## Notes

- Search functionality uses Laravel Scout for fast full-text searching on pattern and notes fields
- Regex patterns are validated before saving to prevent invalid expressions
- Filter application logic is handled by the content filtering service, not the API
- All timestamps are in UTC format
- Creator information is automatically set to the authenticated user
