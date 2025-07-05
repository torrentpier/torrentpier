---
title: Emoji Aliases
sidebar_position: 2
---

# Emoji Aliases API

The Emoji Aliases API allows you to manage alternative shortcodes for emojis, enabling multiple text triggers to map to the same emoji (e.g., `:happy:`, `:joy:`, `:lol:` all pointing to 😂).

## Base URL

```text
/api/emoji/aliases
```

## Endpoints

### List Aliases

Get a paginated list of emoji aliases with optional filtering and relationship loading.

```text
GET /api/emoji/aliases
```

#### Query Parameters

| Parameter    | Type    | Description                            |
|--------------|---------|----------------------------------------|
| `emoji_id`   | integer | Filter by emoji ID                     |
| `search`     | string  | Search in alias text                   |
| `with_emoji` | boolean | Include emoji and category information |
| `page`       | integer | Page number (default: 1)               |
| `per_page`   | integer | Items per page (default: 50, max: 100) |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/emoji/aliases?emoji_id=1&with_emoji=1" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": [
    {
      "id": 1,
      "alias": ":grin:",
      "emoji": {
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
        "aliases": null,
        "aliases_count": null,
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
      },
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    },
    {
      "id": 2,
      "alias": ":happy:",
      "emoji": {
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
        "aliases": null,
        "aliases_count": null,
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
      },
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    }
  ],
  "links": {
    "first": "https://api.example.com/api/emoji/aliases?page=1",
    "last": "https://api.example.com/api/emoji/aliases?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 2
  }
}
```

---

### Search Aliases

Search emoji aliases using Laravel Scout for full-text search capabilities.

```text
GET /api/emoji/aliases/search
```

#### Query Parameters

| Parameter    | Type    | Required | Description                               |
|--------------|---------|----------|-------------------------------------------|
| `q`          | string  | Yes      | Search query (minimum 1 character)        |
| `limit`      | integer | No       | Number of results (default: 20, max: 100) |
| `with_emoji` | boolean | No       | Include emoji and category information    |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/emoji/aliases/search?q=hap&limit=5&with_emoji=1" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": [
    {
      "id": 2,
      "alias": ":happy:",
      "emoji": {
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
        "aliases": null,
        "aliases_count": null,
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
      },
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    }
  ]
}
```

---

### Get Alias

Get a specific emoji alias by ID with full relationship data.

```text
GET /api/emoji/aliases/{id}
```

#### Path Parameters

| Parameter | Type    | Description  |
|-----------|---------|--------------|
| `id`      | integer | The alias ID |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/emoji/aliases/1" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": {
    "id": 1,
    "alias": ":grin:",
    "emoji": {
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
      "aliases": null,
      "aliases_count": null,
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    },
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
  }
}
```

---

### Create Alias

Create a new alias for an existing emoji.

```text
POST /api/emoji/aliases
```

#### Request Body

| Field      | Type    | Required | Description                                          |
|------------|---------|----------|------------------------------------------------------|
| `emoji_id` | integer | Yes      | The emoji ID this alias points to                    |
| `alias`    | string  | Yes      | The alias in `:name:` format (max 255 chars, unique) |

#### Example Request

```bash
curl -X POST "https://api.example.com/api/emoji/aliases" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "emoji_id": 1,
    "alias": ":cheerful:"
  }'
```

#### Example Response

```json
{
  "data": {
    "id": 3,
    "alias": ":cheerful:",
    "emoji": null,
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:00:00Z"
  }
}
```

---

### Update Alias

Update an existing emoji alias.

```text
PUT /api/emoji/aliases/{id}
PATCH /api/emoji/aliases/{id}
```

#### Path Parameters

| Parameter | Type    | Description  |
|-----------|---------|--------------|
| `id`      | integer | The alias ID |

#### Request Body

| Field      | Type    | Required | Description                                          |
|------------|---------|----------|------------------------------------------------------|
| `emoji_id` | integer | No       | The emoji ID this alias points to                    |
| `alias`    | string  | No       | The alias in `:name:` format (max 255 chars, unique) |

#### Example Request

```bash
curl -X PATCH "https://api.example.com/api/emoji/aliases/3" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "alias": ":joyful:"
  }'
```

#### Example Response

```json
{
  "data": {
    "id": 3,
    "alias": ":joyful:",
    "emoji": null,
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:30:00Z"
  }
}
```

---

### Delete Alias

Delete an emoji alias. The associated emoji remains unchanged.

```text
DELETE /api/emoji/aliases/{id}
```

#### Path Parameters

| Parameter | Type    | Description  |
|-----------|---------|--------------|
| `id`      | integer | The alias ID |

#### Example Request

```bash
curl -X DELETE "https://api.example.com/api/emoji/aliases/3" \
  -H "Accept: application/json"
```

#### Example Response

```text
HTTP/1.1 204 No Content
```

## Error Responses

### 404 Not Found

```json
{
  "message": "No query results for model [App\\Models\\EmojiAlias] 999"
}
```

### 422 Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "emoji_id": ["The selected emoji id is invalid."],
    "alias": [
      "The alias must be in the format :name: (e.g., :happy:)",
      "This alias is already taken.",
      "This alias conflicts with an existing emoji shortcode."
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

## Use Cases

Aliases are useful for creating alternative ways to access the same emoji:

### 1. Multiple Language Support

```json
{
  "emoji_shortcode": ":smile:",
  "aliases": [":sonrisa:", ":sourire:", ":笑顔:"]
}
```

### 2. Slack-style Flexibility

```json
{
  "emoji_shortcode": ":thumbsup:",
  "aliases": [":+1:", ":like:", ":approve:"]
}
```

### 3. Legacy Compatibility

```json
{
  "emoji_shortcode": ":laughing:",
  "aliases": [":lol:", ":rofl:", ":-D"]
}
```

### 4. Common Misspellings

```json
{
  "emoji_shortcode": ":receive:",
  "aliases": [":recieve:", ":recive:"]
}
```

## Validation Rules

- **alias**: Must be unique across all aliases and cannot conflict with any emoji shortcode
- **Format**: Must follow the `:name:` pattern using letters, numbers, underscores, and hyphens
- **emoji_id**: Must reference an existing emoji
- **Cross-table uniqueness**: Aliases cannot use the same text as any emoji's primary shortcode

## Notes

- Aliases are returned ordered by `alias` alphabetically
- When an emoji is deleted, all its aliases are automatically deleted (cascade)
- Search functionality uses Laravel Scout for fast full-text search
- Maximum search results per request is 100
- Aliases can be reassigned to different emojis by updating the `emoji_id`
- The same alias text cannot exist more than once in the system
