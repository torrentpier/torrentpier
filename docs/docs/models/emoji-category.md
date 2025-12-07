---
sidebar_position: 3
title: EmojiCategory
---

# EmojiCategory Model

The `EmojiCategory` model represents a category for grouping emojis in the editor (e.g., "Smileys", "Animals", "Food").

## Model Properties

### Table Name
- `emoji_categories`

### Fillable Fields
- `title` - Category name (e.g., "Smileys & Emotion")
- `display_order` - Integer defining the order of the category in the editor

### Timestamps
- `created_at`
- `updated_at`

## Relationships

### Has Many: Emoji

```php
public function emojis(): HasMany
{
    return $this->hasMany(Emoji::class);
}
```

Each category can contain multiple emojis.

## Usage Examples

### Creating a Category

```php
use App\Models\EmojiCategory;

$category = EmojiCategory::create([
    'title' => 'Smileys & Emotion',
    'display_order' => 1
]);
```

### Retrieving Categories with Emoji

```php
// Get all categories ordered by display order
$categories = EmojiCategory::orderBy('display_order')->get();

// Get category with all its emojis
$category = EmojiCategory::with('emojis')->find(1);

// Get category with emojis ordered
$category = EmojiCategory::with(['emojis' => function ($query) {
    $query->orderBy('display_order');
}])->find(1);
```

### Accessing Related Emoji

```php
$category = EmojiCategory::find(1);

// Access emojis via dynamic property
foreach ($category->emojis as $emoji) {
    echo $emoji->emoji_shortcode;
}

// Query emojis with additional constraints
$activeEmojis = $category->emojis()
    ->whereNotNull('emoji_text')
    ->get();
```

## Database Schema

```sql
CREATE TABLE emoji_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    display_order INTEGER NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_display_order (display_order)
);
```

## Factory

The model includes a factory for testing:

```php
use App\Models\EmojiCategory;

// Create a single category
$category = EmojiCategory::factory()->create();

// Create multiple categories
$categories = EmojiCategory::factory()->count(5)->create();

// Create with specific attributes
$category = EmojiCategory::factory()->create([
    'title' => 'Custom Emoji',
    'display_order' => 10
]);
```

## Notes

- The `display_order` field is indexed for performance when ordering categories
- Categories can be soft-deleted if needed in future implementations
- When an emoji category is deleted, associated emojis will have their `emoji_category_id` set to null (not cascade delete)
