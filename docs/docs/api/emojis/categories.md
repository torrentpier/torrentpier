---
title: Emoji Categories API
sidebar_position: 3
---

# Emoji Categories API

The Emoji Categories API allows you to manage emoji categories used to organize emojis in the editor interface.

## Base URL

```
/api/emoji/categories
```

## Endpoints

### List Categories

Get a list of all emoji categories.

```http
GET /api/emoji/categories
```

#### Query Parameters

| Parameter        | Type    | Description                               |
|------------------|---------|-------------------------------------------|
| `with_emojis`    | boolean | Include emoji count for each category     |
| `include_emojis` | boolean | Include full emoji data for each category |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/emoji/categories?with_emojis=1" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Smileys",
      "display_order": 1,
      "emojis_count": 15,
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    },
    {
      "id": 2,
      "title": "People",
      "display_order": 2,
      "emojis_count": 8,
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    }
  ]
}
```

---

### Get Category

Get a specific emoji category by ID.

```http
GET /api/emoji/categories/{id}
```

#### Path Parameters

| Parameter | Type    | Description     |
|-----------|---------|-----------------|
| `id`      | integer | The category ID |

#### Query Parameters

| Parameter      | Type    | Description                               |
|----------------|---------|-------------------------------------------|
| `with_aliases` | boolean | Include emoji aliases when loading emojis |

#### Example Request

```bash
curl -X GET "https://api.example.com/api/emoji/categories/1?with_aliases=1" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "data": {
    "id": 1,
    "title": "Smileys",
    "display_order": 1,
    "emojis": [
      {
        "id": 1,
        "title": "Grinning",
        "emoji_text": "😀",
        "emoji_shortcode": ":grinning:",
        "image_url": null,
        "sprite_mode": false,
        "sprite_params": null,
        "display_order": 1,
        "aliases": [
          {
            "id": 1,
            "alias": ":grin:",
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-01T00:00:00Z"
          }
        ],
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
      }
    ],
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
  }
}
```

---

### Create Category

Create a new emoji category.

```http
POST /api/emoji/categories
```

#### Request Body

| Field           | Type    | Required | Description                   |
|-----------------|---------|----------|-------------------------------|
| `title`         | string  | Yes      | Category name (max 255 chars) |
| `display_order` | integer | Yes      | Display order (minimum 0)     |

#### Example Request

```bash
curl -X POST "https://api.example.com/api/emoji/categories" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Custom Category",
    "display_order": 10
  }'
```

#### Example Response

```json
{
  "data": {
    "id": 3,
    "title": "Custom Category",
    "display_order": 10,
    "emojis_count": null,
    "emojis": null,
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:00:00Z"
  }
}
```

---

### Update Category

Update an existing emoji category.

```http
PUT /api/emoji/categories/{id}
PATCH /api/emoji/categories/{id}
```

#### Path Parameters

| Parameter | Type    | Description     |
|-----------|---------|-----------------|
| `id`      | integer | The category ID |

#### Request Body

| Field           | Type    | Required | Description                   |
|-----------------|---------|----------|-------------------------------|
| `title`         | string  | No       | Category name (max 255 chars) |
| `display_order` | integer | No       | Display order (minimum 0)     |

#### Example Request

```bash
curl -X PATCH "https://api.example.com/api/emoji/categories/3" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Updated Category Name"
  }'
```

#### Example Response

```json
{
  "data": {
    "id": 3,
    "title": "Updated Category Name",
    "display_order": 10,
    "emojis_count": null,
    "emojis": null,
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:30:00Z"
  }
}
```

---

### Delete Category

Delete an emoji category. Associated emojis will have their category_id set to null.

```http
DELETE /api/emoji/categories/{id}
```

#### Path Parameters

| Parameter | Type    | Description     |
|-----------|---------|-----------------|
| `id`      | integer | The category ID |

#### Example Request

```bash
curl -X DELETE "https://api.example.com/api/emoji/categories/3" \
  -H "Accept: application/json"
```

#### Example Response

```
HTTP/1.1 204 No Content
```

## Error Responses

### 404 Not Found

```json
{
  "message": "No query results for model [App\\Models\\EmojiCategory] 999"
}
```

### 422 Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "display_order": ["The display order must be at least 0."]
  }
}
```

## Notes

- Categories are returned ordered by `display_order` ascending
- When a category is deleted, associated emojis remain but their `emoji_category_id` is set to null
- The `emojis_count` field is only included when `with_emojis=1` parameter is used
- The `emojis` field is only included when `include_emojis=1` parameter is used
