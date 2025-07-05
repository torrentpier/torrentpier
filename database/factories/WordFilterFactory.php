<?php

namespace Database\Factories;

use App\Models\WordFilter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WordFilter>
 */
class WordFilterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filterType = $this->faker->randomElement(WordFilter::getFilterTypes());
        $patternType = $this->faker->randomElement(WordFilter::getPatternTypes());

        // Generate appropriate pattern based on type
        $pattern = match ($patternType) {
            'exact' => $this->faker->word(),
            'wildcard' => '*' . $this->faker->word() . '*',
            'regex' => '/\\b' . $this->faker->word() . '\\b/i',
        };

        return [
            'pattern' => $pattern,
            'replacement' => $filterType === 'replace' ? str_repeat('*', strlen($this->faker->word())) : null,
            'filter_type' => $filterType,
            'pattern_type' => $patternType,
            'severity' => $this->faker->randomElement(WordFilter::getSeverityLevels()),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'case_sensitive' => $this->faker->boolean(20), // 20% chance of being case-sensitive
            'applies_to' => $this->faker->randomElements(
                WordFilter::getContentTypes(),
                $this->faker->numberBetween(1, 3)
            ),
            'created_by' => null,
            'notes' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
        ];
    }
}
