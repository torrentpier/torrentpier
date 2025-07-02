---
sidebar_position: 1
title: Emoji
---

# Emoji Model

The `Emoji` model represents an individual emoji, which can be a Unicode emoji (😊), legacy text emoticon (:-)), or a custom image.

## Model Properties

### Table Name
- `emojis`

### Fillable Fields
- `title` - Human-readable name (e.g., "Smile", "Thumbs Up")
- `emoji_text` - Unicode character or text emoticon (nullable)
- `emoji_shortcode` - Primary shortcode (e.g., `:smile:`) - unique
- `image_url` - Path to custom image (nullable)
- `sprite_mode` - Boolean flag for CSS sprite usage
- `sprite_params` - JSON field for sprite parameters
- `emoji_category_id` - Foreign key to category (nullable)
- `display_order` - Integer for ordering within category

### Casts
- `sprite_mode` → boolean
- `sprite_params` → array

### Timestamps
- `created_at`
- `updated_at`

## Traits

### Searchable (Laravel Scout)
The model uses Laravel Scout for full-text search functionality.

```php
public function toSearchableArray()
{
    return [
        'id' => $this->id,
        'emoji_shortcode' => $this->emoji_shortcode,
        'emoji_text' => $this->emoji_text,
    ];
}
```

## Relationships

### Belongs To: Category

```php
public function category(): BelongsTo
{
    return $this->belongsTo(EmojiCategory::class, 'emoji_category_id');
}
```

### Has Many: Aliases

```php
public function aliases(): HasMany
{
    return $this->hasMany(EmojiAlias::class);
}
```

## Usage Examples

### Creating Emoji

```php
use App\Models\Emoji;
use App\Models\EmojiCategory;

// Create a Unicode emoji
$emoji = Emoji::create([
    'title' => 'Grinning Face',
    'emoji_text' => '😀',
    'emoji_shortcode' => ':grinning:',
    'emoji_category_id' => $category->id,
    'display_order' => 1
]);

// Create a custom image emoji
$customEmoji = Emoji::create([
    'title' => 'Party Parrot',
    'emoji_shortcode' => ':partyparrot:',
    'image_url' => '/emojis/custom/partyparrot.gif',
    'emoji_category_id' => $category->id,
    'display_order' => 2
]);

// Create a sprite-based emoji
$spriteEmoji = Emoji::create([
    'title' => 'Custom Sprite',
    'emoji_shortcode' => ':custom:',
    'sprite_mode' => true,
    'sprite_params' => [
        'x' => 32,
        'y' => 64,
        'width' => 32,
        'height' => 32,
        'sheet' => 'emoji-sheet-1.png'
    ],
    'emoji_category_id' => $category->id,
    'display_order' => 3
]);
```

### Retrieving Emoji

```php
// Find by shortcode (remember it's unique)
$emoji = Emoji::where('emoji_shortcode', ':smile:')->first();

// Get all emojis with their aliases
$emojis = Emoji::with('aliases')->get();

// Get emojis in a specific category
$categoryEmojis = Emoji::where('emoji_category_id', $categoryId)
    ->orderBy('display_order')
    ->get();

// Get only Unicode emojis
$unicodeEmojis = Emoji::whereNotNull('emoji_text')
    ->whereNull('image_url')
    ->get();

// Get only custom image emojis
$customEmojis = Emoji::whereNull('emoji_text')
    ->whereNotNull('image_url')
    ->get();
```

### Working with Aliases

```php
$emoji = Emoji::find(1);

// Access aliases
foreach ($emoji->aliases as $alias) {
    echo $alias->alias; // e.g., ":happy:", ":joy:"
}

// Add a new alias
$emoji->aliases()->create([
    'alias' => ':new-alias:'
]);
```

### Search Integration

```php
// Search for emojis using Scout
$results = Emoji::search(':smile')->get();
$results = Emoji::search('😊')->get();

// Update search index
$emoji->searchable(); // Add to index
$emoji->unsearchable(); // Remove from index
```

## Database Schema

```sql
CREATE TABLE emojis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    emoji_text VARCHAR(255) NULL,
    emoji_shortcode VARCHAR(255) NOT NULL UNIQUE,
    image_url VARCHAR(255) NULL,
    sprite_mode BOOLEAN DEFAULT FALSE,
    sprite_params JSON NULL,
    emoji_category_id BIGINT UNSIGNED NULL,
    display_order INTEGER NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_display_order (display_order),
    INDEX idx_emoji_category_id (emoji_category_id),
    FOREIGN KEY (emoji_category_id) REFERENCES emoji_categories(id) ON DELETE SET NULL
);
```

## Factory

The model includes a factory with useful states:

```php
use App\Models\Emoji;

// Create a random emoji
$emoji = Emoji::factory()->create();

// Create a custom image emoji
$customEmoji = Emoji::factory()->customImage()->create();

// Create a sprite-based emoji
$spriteEmoji = Emoji::factory()->withSprite()->create();

// Create multiple emojis with a category
$emojis = Emoji::factory()
    ->count(10)
    ->for(EmojiCategory::factory())
    ->create();
```

## Performance Considerations

- The `emoji_shortcode` field has a unique index for fast lookups during text replacement
- The `display_order` field is indexed for efficient ordering
- The `emoji_category_id` field is indexed for category-based queries
- Scout integration provides full-text search capabilities

## Future Enhancements

The following helper methods should be implemented in service classes:

1. **Text Replacement Helper** - Get all possible text triggers (shortcode + aliases)
2. **Render Helper** - Determine render method (unicode, image, or sprite)
3. **Eager Loading Scope** - Efficiently load emojis with aliases for replacement engine
4. **Type Check Helper** - Determine if emoji is unicode, custom image, or sprite

Example service method signatures:
```php
// EmojiService
public function getAllTriggers(Emoji $emoji): array;
public function getRenderData(Emoji $emoji): array;
public function loadForReplacement(): Collection;
public function isCustomEmoji(Emoji $emoji): bool;
```
