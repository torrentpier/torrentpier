<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class WordFilter extends Model
{
    /** @use HasFactory<\Database\Factories\WordFilterFactory> */
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pattern',
        'replacement',
        'filter_type',
        'pattern_type',
        'severity',
        'is_active',
        'case_sensitive',
        'applies_to',
        'created_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'case_sensitive' => 'boolean',
        'applies_to' => 'array',
    ];

    /**
     * Filter type constants.
     */
    public const string FILTER_TYPE_REPLACE = 'replace';

    public const string FILTER_TYPE_BLOCK = 'block';

    public const string FILTER_TYPE_MODERATE = 'moderate';

    /**
     * Pattern type constants.
     */
    public const string PATTERN_TYPE_EXACT = 'exact';

    public const string PATTERN_TYPE_WILDCARD = 'wildcard';

    public const string PATTERN_TYPE_REGEX = 'regex';

    /**
     * Severity constants.
     */
    public const string SEVERITY_LOW = 'low';

    public const string SEVERITY_MEDIUM = 'medium';

    public const string SEVERITY_HIGH = 'high';

    /**
     * Content type constants for applies_to field.
     */
    public const string APPLIES_TO_POSTS = 'posts';

    public const string APPLIES_TO_PRIVATE_MESSAGES = 'private_messages';

    public const string APPLIES_TO_USERNAMES = 'usernames';

    public const string APPLIES_TO_SIGNATURES = 'signatures';

    public const string APPLIES_TO_PROFILE_FIELDS = 'profile_fields';

    /**
     * Get all available filter types.
     *
     * @return array<string>
     */
    public static function getFilterTypes(): array
    {
        return [
            self::FILTER_TYPE_REPLACE,
            self::FILTER_TYPE_BLOCK,
            self::FILTER_TYPE_MODERATE,
        ];
    }

    /**
     * Get all available pattern types.
     *
     * @return array<string>
     */
    public static function getPatternTypes(): array
    {
        return [
            self::PATTERN_TYPE_EXACT,
            self::PATTERN_TYPE_WILDCARD,
            self::PATTERN_TYPE_REGEX,
        ];
    }

    /**
     * Get all available severity levels.
     *
     * @return array<string>
     */
    public static function getSeverityLevels(): array
    {
        return [
            self::SEVERITY_LOW,
            self::SEVERITY_MEDIUM,
            self::SEVERITY_HIGH,
        ];
    }

    /**
     * Get all available content types.
     *
     * @return array<string>
     */
    public static function getContentTypes(): array
    {
        return [
            self::APPLIES_TO_POSTS,
            self::APPLIES_TO_PRIVATE_MESSAGES,
            self::APPLIES_TO_USERNAMES,
            self::APPLIES_TO_SIGNATURES,
            self::APPLIES_TO_PROFILE_FIELDS,
        ];
    }

    /**
     * Get the user who created the filter.
     *
     * @return BelongsTo<User, WordFilter>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'pattern' => $this->pattern,
            'notes' => $this->notes,
            'filter_type' => $this->filter_type,
            'severity' => $this->severity,
        ];
    }
}
