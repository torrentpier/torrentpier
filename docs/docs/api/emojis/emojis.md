---
title: Emoji API
sidebar_position: 1
---

# Emoji API

The Emoji API allows you to manage individual emojis, including Unicode emojis, custom images, and CSS sprite-based emojis.

## Base URL

```http
/api/emoji/emojis
```

## Endpoints

### List Emoji

Get a paginated list of emojis with optional filtering and relationship loading.

```http
GET /api/emoji/emojis
```

#### Query Parameters

| Parameter       | Type    | Description                               |
|-----------------|---------|-------------------------------------------|
| `category_id`   | integer | Filter by category ID                     |
| `search`        | string  | Search in shortcode, title, or emoji text |
| `with_category` | boolean | Include category information              |
| `with_aliases`  | boolean | Include emoji aliases                     |
| `page`          | integer | Page number (default: 1)                  |
| `per_page`      | integer | Items per page (default: 50, max: 100)    |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/emoji/emojis?category_id=1&with_aliases=1&per_page=25" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Grinning Face",
      "emoji_text": "😀",
      "emoji_shortcode": ":grinning:",
      "image_url": null,
      "sprite_mode": false,
      "sprite_params": null,
      "display_order": 1,
      "category": {
        "id": 1,
        "title": "Smileys",
        "display_order": 1,
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
      },
      "aliases": [
        {
          "id": 1,
          "alias": ":grin:",
          "created_at": "2025-01-01T00:00:00Z",
          "updated_at": "2025-01-01T00:00:00Z"
        }
      ],
      "aliases_count": 1,
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    }
  ],
  "links": {
    "first": "https://api.example.com/api/emoji/emojis?page=1",
    "last": "https://api.example.com/api/emoji/emojis?page=3",
    "prev": null,
    "next": "https://api.example.com/api/emoji/emojis?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 67
  }
}
```

---

### Search Emoji

Search emojis using Laravel Scout for full-text search capabilities.

```http
GET /api/emoji/emojis/search
```

#### Query Parameters

| Parameter       | Type    | Required | Description                               |
|-----------------|---------|----------|-------------------------------------------|
| `q`             | string  | Yes      | Search query (minimum 1 character)        |
| `limit`         | integer | No       | Number of results (default: 20, max: 100) |
| `with_category` | boolean | No       | Include category information              |
| `with_aliases`  | boolean | No       | Include emoji aliases                     |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/emoji/emojis/search?q=smile&limit=10&with_category=1" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Smiling Face",
      "emoji_text": "😊",
      "emoji_shortcode": ":smile:",
      "image_url": null,
      "sprite_mode": false,
      "sprite_params": null,
      "display_order": 2,
      "category": {
        "id": 1,
        "title": "Smileys",
        "display_order": 1,
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
      },
      "aliases": null,
      "aliases_count": null,
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    }
  ]
}
```

---

### Get Emoji

Get a specific emoji by ID with full relationship data.

```http
GET /api/emoji/emojis/{id}
```

#### Path Parameters

| Parameter | Type    | Description  |
|-----------|---------|--------------|
| `id`      | integer | The emoji ID |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/emoji/emojis/1" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": {
    "id": 1,
    "title": "Grinning Face",
    "emoji_text": "😀",
    "emoji_shortcode": ":grinning:",
    "image_url": null,
    "sprite_mode": false,
    "sprite_params": null,
    "display_order": 1,
    "category": {
      "id": 1,
      "title": "Smileys",
      "display_order": 1,
      "emojis_count": null,
      "emojis": null,
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    },
    "aliases": [
      {
        "id": 1,
        "alias": ":grin:",
        "emoji": null,
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
      }
    ],
    "aliases_count": null,
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
  }
}
```

---

### Create Emoji

Create a new emoji. Supports Unicode emojis, custom images, and CSS sprites.

```http
POST /api/emoji/emojis
```

#### Request Body

| Field                  | Type    | Required    | Description                                             |
|------------------------|---------|-------------|---------------------------------------------------------|
| `title`                | string  | Yes         | Human-readable name (max 255 chars)                     |
| `emoji_text`           | string  | No          | Unicode emoji or text emoticon (max 10 chars)           |
| `emoji_shortcode`      | string  | Yes         | Primary shortcode in `:name:` format (unique)           |
| `image_url`            | string  | No          | Path to custom image (max 500 chars)                    |
| `sprite_mode`          | boolean | No          | Whether to use CSS sprite mode (default: false)         |
| `sprite_params`        | object  | No          | Sprite parameters (required if sprite_mode is true)     |
| `sprite_params.x`      | integer | Conditional | X coordinate (required if sprite_mode is true)          |
| `sprite_params.y`      | integer | Conditional | Y coordinate (required if sprite_mode is true)          |
| `sprite_params.width`  | integer | Conditional | Sprite width (required if sprite_mode is true)          |
| `sprite_params.height` | integer | Conditional | Sprite height (required if sprite_mode is true)         |
| `sprite_params.sheet`  | string  | Conditional | Sprite sheet filename (required if sprite_mode is true) |
| `emoji_category_id`    | integer | No          | Category ID (must exist)                                |
| `display_order`        | integer | Yes         | Display order within category (minimum 0)               |

#### Example Requests

**Unicode Emoji:**

```bash
curl -X POST "https://api.example.com/api/emoji/emojis" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Heart Eyes",
    "emoji_text": "😍",
    "emoji_shortcode": ":heart_eyes:",
    "emoji_category_id": 1,
    "display_order": 5
  }'
```

**Custom Image Emoji:**

```bash
curl -X POST "https://api.example.com/api/emoji/emojis" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Party Parrot",
    "emoji_shortcode": ":partyparrot:",
    "image_url": "/emojis/custom/partyparrot.gif",
    "emoji_category_id": 2,
    "display_order": 1
  }'
```

**CSS Sprite Emoji:**

```bash
curl -X POST "https://api.example.com/api/emoji/emojis" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Custom Sprite",
    "emoji_shortcode": ":custom_sprite:",
    "sprite_mode": true,
    "sprite_params": {
      "x": 32,
      "y": 64,
      "width": 32,
      "height": 32,
      "sheet": "emoji-sheet-1.png"
    },
    "emoji_category_id": 3,
    "display_order": 10
  }'
```

#### Example Response

```json
{
  "data": {
    "id": 25,
    "title": "Heart Eyes",
    "emoji_text": "😍",
    "emoji_shortcode": ":heart_eyes:",
    "image_url": null,
    "sprite_mode": false,
    "sprite_params": null,
    "display_order": 5,
    "category": null,
    "aliases": null,
    "aliases_count": null,
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:00:00Z"
  }
}
```

---

### Update Emoji

Update an existing emoji.

```http
PUT /api/emoji/emojis/{id}
PATCH /api/emoji/emojis/{id}
```

#### Path Parameters

| Parameter | Type    | Description  |
|-----------|---------|--------------|
| `id`      | integer | The emoji ID |

#### Request Body

All fields from the create endpoint are supported, but all are optional for updates.

#### Example Request

```bash
curl -X PATCH "https://api.example.com/api/emoji/emojis/25" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Love Eyes",
    "display_order": 6
  }'
```

#### Example Response

```json
{
  "data": {
    "id": 25,
    "title": "Love Eyes",
    "emoji_text": "😍",
    "emoji_shortcode": ":heart_eyes:",
    "image_url": null,
    "sprite_mode": false,
    "sprite_params": null,
    "display_order": 6,
    "category": null,
    "aliases": null,
    "aliases_count": null,
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:30:00Z"
  }
}
```

---

### Delete Emoji

Delete an emoji. Associated aliases will be automatically deleted.

```http
DELETE /api/emoji/emojis/{id}
```

#### Path Parameters

| Parameter | Type    | Description  |
|-----------|---------|--------------|
| `id`      | integer | The emoji ID |

#### Example Request

```bash
curl -X DELETE "https://api.example.com/api/emoji/emojis/25" \
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
    "emoji_shortcode": [
      "The emoji shortcode must be in the format :name: (e.g., :smile:)",
      "This shortcode is already taken."
    ],
    "sprite_params.x": [
      "The sprite params.x field is required when sprite mode is true."
    ]
  }
}
```

### 400 Search Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "q": ["The q field is required."],
    "limit": ["The limit must not be greater than 100."]
  }
}
```

## Emoji Types

The API supports three types of emojis:

### 1. Unicode Emoji
- Have `emoji_text` field set to the Unicode character
- `image_url` and `sprite_params` are null
- `sprite_mode` is false

### 2. Custom Image Emoji
- Have `image_url` field set to the image path
- `emoji_text` and `sprite_params` are null
- `sprite_mode` is false

### 3. CSS Sprite Emoji
- Have `sprite_mode` set to true
- Have `sprite_params` object with position and dimensions
- `emoji_text` and `image_url` are null

## Validation Rules

- **emoji_shortcode**: Must be unique across all emojis and cannot conflict with any alias
- **Aliases**: Cannot conflict with any existing emoji shortcode
- **Format**: All shortcodes and aliases must follow the `:name:` pattern
- **Sprite params**: Required when `sprite_mode` is true
- **Category**: Must reference an existing category if provided

## Notes

- Emoji are returned ordered by `display_order` ascending
- When an emoji is deleted, all associated aliases are automatically deleted (cascade)
- Search functionality uses Laravel Scout for fast full-text search
- The `aliases_count` field is only included when aliases are not loaded
- Maximum search results per request is 100
