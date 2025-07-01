---
sidebar_position: 2
title: EmojiAlias
---

# EmojiAlias Model

The `EmojiAlias` model represents additional text aliases for an emoji, allowing multiple shortcodes to map to the same emoji (e.g., `:happy:`, `:joy:`, `:lol:` all mapping to 😂).

## Model Properties

### Table Name
- `emoji_aliases`

### Fillable Fields
- `emoji_id` - Foreign key to the associated emoji
- `alias` - Alternative shortcode (e.g., `:happy:`) - unique

### Timestamps
- `created_at`
- `updated_at`

## Traits

### Searchable (Laravel Scout)
The model uses Laravel Scout for alias search functionality.

```php
public function toSearchableArray()
{
    return [
        'id' => $this->id,
        'alias' => $this->alias,
        'emoji_id' => $this->emoji_id,
    ];
}
```

## Relationships

### Belongs To: Emoji

```php
public function emoji(): BelongsTo
{
    return $this->belongsTo(Emoji::class);
}
```

## Usage Examples

### Creating Aliases

```php
use App\Models\EmojiAlias;
use App\Models\Emoji;

// Create an alias for an existing emoji
$emoji = Emoji::where('emoji_shortcode', ':joy:')->first();

$alias = EmojiAlias::create([
    'emoji_id' => $emoji->id,
    'alias' => ':lol:'
]);

// Create multiple aliases via the emoji relationship
$emoji->aliases()->createMany([
    ['alias' => ':laughing:'],
    ['alias' => ':rofl:'],
    ['alias' => ':lmao:']
]);
```

### Retrieving Aliases

```php
// Find emoji by alias
$alias = EmojiAlias::where('alias', ':lol:')->first();
$emoji = $alias->emoji;

// Get all aliases for an emoji
$emoji = Emoji::find(1);
$aliases = $emoji->aliases;

// Search for aliases
$results = EmojiAlias::search(':hap')->get();
```

### Working with Emoji Through Alias

```php
// Get the emoji details from an alias
$alias = EmojiAlias::with('emoji')->where('alias', ':lol:')->first();

echo $alias->emoji->title;           // "Laughing"
echo $alias->emoji->emoji_text;      // "😂"
echo $alias->emoji->emoji_shortcode; // ":joy:"
```

## Database Schema

```sql
CREATE TABLE emoji_aliases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    emoji_id BIGINT UNSIGNED NOT NULL,
    alias VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_emoji_id_alias (emoji_id, alias),
    FOREIGN KEY (emoji_id) REFERENCES emojis(id) ON DELETE CASCADE
);
```

## Factory

The model includes a factory for testing:

```php
use App\Models\EmojiAlias;
use App\Models\Emoji;

// Create an alias with a new emoji
$alias = EmojiAlias::factory()->create();

// Create an alias for an existing emoji
$emoji = Emoji::factory()->create();
$alias = EmojiAlias::factory()->create([
    'emoji_id' => $emoji->id,
    'alias' => ':custom-alias:'
]);

// Create multiple aliases
$aliases = EmojiAlias::factory()
    ->count(5)
    ->for($emoji)
    ->create();
```

## Performance Considerations

- The `alias` field has a unique index for fast lookups during text replacement
- The composite index on `(emoji_id, alias)` optimizes join queries
- Cascade delete ensures aliases are removed when an emoji is deleted
- Scout integration enables fast alias searching

## Validation Considerations

When implementing the emoji management system, consider these validation rules:

1. **Uniqueness Across Tables** - An alias should not match any existing `emoji_shortcode`
2. **Format Validation** - Aliases should follow the `:name:` format
3. **Reserved Keywords** - Certain aliases might be reserved for system use

Example validation in a request class:

```php
public function rules()
{
    return [
        'alias' => [
            'required',
            'string',
            'regex:/^:[a-zA-Z0-9_-]+:$/',
            'unique:emoji_aliases,alias',
            Rule::notIn(Emoji::pluck('emoji_shortcode')->toArray()),
        ],
    ];
}
```

## Use Cases

1. **Slack-style Flexibility** - Users can type `:+1:`, `:thumbsup:`, or `:like:` for 👍
2. **Legacy Support** - Map old emoticon codes to new emoji system
3. **Localization** - Different languages can have their own aliases
4. **User Preferences** - Users could potentially create personal aliases

## Notes

- Aliases are automatically deleted when their parent emoji is deleted (cascade)
- Each alias must be unique across the entire system
- The system should validate that an alias doesn't conflict with any emoji shortcode
- Consider implementing a maximum number of aliases per emoji for performance
