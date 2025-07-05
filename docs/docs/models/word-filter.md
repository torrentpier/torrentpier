---
sidebar_position: 4
title: WordFilter
---

# WordFilter Model

The `WordFilter` model represents content moderation rules that can automatically replace, block, or flag content based on pattern matching. It supports exact matches, wildcard patterns, and regular expressions across various content types.

## Model Properties

### Table Name
- `word_filters`

### Fillable Fields
- `pattern` - The text pattern to match (unique)
- `replacement` - Replacement text for 'replace' type filters (nullable)
- `filter_type` - Action to take: 'replace', 'block', or 'moderate'
- `pattern_type` - Pattern matching type: 'exact', 'wildcard', or 'regex'
- `severity` - Impact level: 'low', 'medium', or 'high'
- `is_active` - Boolean flag to enable/disable the filter
- `case_sensitive` - Boolean flag for case-sensitive matching
- `applies_to` - JSON array of content types this filter applies to
- `notes` - Optional description or explanation (nullable)
- `creator_id` - Foreign key to user who created the filter (nullable)

### Casts
- `is_active` → boolean
- `case_sensitive` → boolean
- `applies_to` → array

### Enums
- `filter_type`: 'replace', 'block', 'moderate'
- `pattern_type`: 'exact', 'wildcard', 'regex'
- `severity`: 'low', 'medium', 'high'

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
        'pattern' => $this->pattern,
        'replacement' => $this->replacement,
        'filter_type' => $this->filter_type,
        'severity' => $this->severity,
        'notes' => $this->notes,
        'applies_to' => implode(' ', $this->applies_to ?? []),
    ];
}
```

## Relationships

### Belongs To: Creator (User)

```php
public function creator(): BelongsTo
{
    return $this->belongsTo(User::class, 'creator_id');
}
```

## Usage Examples

### Creating Word Filters

```php
use App\Models\WordFilter;

// Create a simple profanity replacement filter
$filter = WordFilter::create([
    'pattern' => 'badword',
    'replacement' => '****',
    'filter_type' => 'replace',
    'pattern_type' => 'exact',
    'severity' => 'medium',
    'is_active' => true,
    'case_sensitive' => false,
    'applies_to' => ['posts', 'private_messages'],
    'notes' => 'Basic profanity filter'
]);

// Create a wildcard spam filter
$spamFilter = WordFilter::create([
    'pattern' => '*viagra*',
    'replacement' => '[SPAM]',
    'filter_type' => 'replace',
    'pattern_type' => 'wildcard',
    'severity' => 'high',
    'is_active' => true,
    'case_sensitive' => false,
    'applies_to' => ['posts', 'private_messages', 'signatures'],
    'notes' => 'Pharmaceutical spam detection'
]);

// Create a regex-based phone number blocker
$phoneFilter = WordFilter::create([
    'pattern' => '/\\b\\d{3}-\\d{3}-\\d{4}\\b/',
    'replacement' => '[phone number]',
    'filter_type' => 'replace',
    'pattern_type' => 'regex',
    'severity' => 'low',
    'is_active' => true,
    'case_sensitive' => false,
    'applies_to' => ['posts'],
    'notes' => 'Phone number privacy protection'
]);

// Create a content moderation flag
$moderateFilter = WordFilter::create([
    'pattern' => 'suspicious',
    'replacement' => null, // No replacement for moderate type
    'filter_type' => 'moderate',
    'pattern_type' => 'exact',
    'severity' => 'medium',
    'is_active' => true,
    'case_sensitive' => false,
    'applies_to' => ['posts'],
    'notes' => 'Flag suspicious content for review'
]);

// Create a complete block filter
$blockFilter = WordFilter::create([
    'pattern' => 'malicious-domain.com',
    'replacement' => null, // No replacement for block type
    'filter_type' => 'block',
    'pattern_type' => 'exact',
    'severity' => 'high',
    'is_active' => true,
    'case_sensitive' => false,
    'applies_to' => ['posts', 'private_messages', 'signatures'],
    'notes' => 'Known malicious domain - block completely'
]);
```

### Retrieving Word Filters

```php
// Get all active filters
$activeFilters = WordFilter::where('is_active', true)->get();

// Get filters by type
$replaceFilters = WordFilter::where('filter_type', 'replace')->get();
$blockFilters = WordFilter::where('filter_type', 'block')->get();

// Get filters by severity
$highSeverity = WordFilter::where('severity', 'high')->get();

// Get filters that apply to specific content
$postFilters = WordFilter::whereJsonContains('applies_to', 'posts')->get();

// Get filters with their creators
$filtersWithCreators = WordFilter::with('creator')->get();

// Search filters using Scout
$searchResults = WordFilter::search('spam')->get();
```

### Working with Pattern Types

```php
// Exact match filters
$exactFilters = WordFilter::where('pattern_type', 'exact')->get();

// Wildcard filters (use * as wildcards)
$wildcardFilters = WordFilter::where('pattern_type', 'wildcard')->get();

// Regex filters (full regular expression support)
$regexFilters = WordFilter::where('pattern_type', 'regex')->get();

// Get case-sensitive filters
$caseSensitive = WordFilter::where('case_sensitive', true)->get();
```

### Applying Filters to Content

```php
// Example service method for applying filters
class ContentModerationService
{
    public function moderateContent(string $content, string $contentType): array
    {
        $filters = WordFilter::where('is_active', true)
            ->whereJsonContains('applies_to', $contentType)
            ->get();
        
        $result = [
            'original' => $content,
            'filtered' => $content,
            'blocked' => false,
            'flagged' => false,
            'applied_filters' => []
        ];
        
        foreach ($filters as $filter) {
            if ($this->matchesPattern($content, $filter)) {
                $result['applied_filters'][] = $filter->id;
                
                switch ($filter->filter_type) {
                    case 'replace':
                        $result['filtered'] = $this->applyReplacement(
                            $result['filtered'], 
                            $filter
                        );
                        break;
                    case 'block':
                        $result['blocked'] = true;
                        return $result; // Stop processing
                    case 'moderate':
                        $result['flagged'] = true;
                        break;
                }
            }
        }
        
        return $result;
    }
}
```

## Database Schema

```sql
CREATE TABLE word_filters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pattern VARCHAR(500) NOT NULL UNIQUE,
    replacement VARCHAR(255) NULL,
    filter_type ENUM('replace', 'block', 'moderate') NOT NULL,
    pattern_type ENUM('exact', 'wildcard', 'regex') NOT NULL,
    severity ENUM('low', 'medium', 'high') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    case_sensitive BOOLEAN DEFAULT FALSE,
    applies_to JSON NOT NULL,
    notes TEXT NULL,
    creator_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_active_type (is_active, filter_type),
    INDEX idx_pattern_type (pattern_type),
    INDEX idx_severity (severity),
    INDEX idx_creator_id (creator_id),
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE SET NULL
);
```

## Factory

The model includes a comprehensive factory with various states:

```php
use App\Models\WordFilter;

// Create a random word filter
$filter = WordFilter::factory()->create();

// Create specific filter types
$replaceFilter = WordFilter::factory()->replace()->create();
$blockFilter = WordFilter::factory()->block()->create();
$moderateFilter = WordFilter::factory()->moderate()->create();

// Create filters with specific pattern types
$exactFilter = WordFilter::factory()->exact()->create();
$wildcardFilter = WordFilter::factory()->wildcard()->create();
$regexFilter = WordFilter::factory()->regex()->create();

// Create inactive filters
$inactiveFilter = WordFilter::factory()->inactive()->create();

// Create high severity filters
$criticalFilter = WordFilter::factory()->highSeverity()->create();

// Create filters for specific content types
$postFilter = WordFilter::factory()->create([
    'applies_to' => ['posts']
]);

// Create multiple filters with a creator
$filters = WordFilter::factory()
    ->count(10)
    ->for(User::factory())
    ->create();
```

## Performance Considerations

- The `pattern` field has a unique index for preventing duplicates
- Composite index on `(is_active, filter_type)` optimizes filtering queries
- JSON index on `applies_to` for content type filtering
- Scout integration provides full-text search capabilities
- Consider caching active filters for high-traffic applications

## Security Considerations

### Regular Expression Safety
- Validate regex patterns to prevent ReDoS (Regular Expression Denial of Service) attacks
- Implement pattern complexity limits
- Use atomic grouping and possessive quantifiers when possible

### Pattern Validation

```php
// Example validation rules
public function rules()
{
    return [
        'pattern' => [
            'required',
            'string',
            'max:500',
            'unique:word_filters,pattern',
            new ValidRegexRule(), // Custom rule for regex validation
        ],
        'replacement' => [
            'required_if:filter_type,replace',
            'nullable',
            'string',
            'max:255',
        ],
    ];
}
```

## Content Types

The `applies_to` field supports these content types:
- `posts` - Forum posts, comments
- `private_messages` - Direct messages between users
- `usernames` - User registration and profile updates
- `signatures` - User signature text
- `profile_fields` - Custom profile field content

## Use Cases

### 1. Profanity Filtering

```php
WordFilter::create([
    'pattern' => 'badword',
    'replacement' => '****',
    'filter_type' => 'replace',
    'pattern_type' => 'exact',
    'applies_to' => ['posts', 'private_messages']
]);
```

### 2. Spam Detection

```php
WordFilter::create([
    'pattern' => '*buy now*',
    'replacement' => '[SPAM]',
    'filter_type' => 'replace',
    'pattern_type' => 'wildcard',
    'applies_to' => ['posts']
]);
```

### 3. Privacy Protection

```php
WordFilter::create([
    'pattern' => '/\b\d{3}-\d{2}-\d{4}\b/', // SSN pattern
    'replacement' => '[SSN]',
    'filter_type' => 'replace',
    'pattern_type' => 'regex',
    'applies_to' => ['posts', 'private_messages']
]);
```

### 4. Content Moderation

```php
WordFilter::create([
    'pattern' => 'report this',
    'filter_type' => 'moderate',
    'pattern_type' => 'exact',
    'applies_to' => ['posts']
]);
```

### 5. Complete Blocking

```php
WordFilter::create([
    'pattern' => 'malicious-site.com',
    'filter_type' => 'block',
    'pattern_type' => 'exact',
    'applies_to' => ['posts', 'private_messages', 'signatures']
]);
```

## Notes

- Filters are processed in order of severity (high → medium → low)
- Block filters immediately stop content processing
- Moderate filters flag content but don't prevent posting
- Replace filters modify content before display
- The `applies_to` array allows granular control over where filters are applied
- Inactive filters are preserved but not processed
- Consider implementing a filter testing interface for administrators
