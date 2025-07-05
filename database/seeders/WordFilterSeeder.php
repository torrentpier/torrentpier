<?php

namespace Database\Seeders;

use App\Models\WordFilter;
use Illuminate\Database\Seeder;

class WordFilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // This seeder adds filters without removing existing ones
        // Demo filters use updateOrCreate to avoid duplicates

        // First, create some specific examples for demonstration
        $this->createDemoFilters();

        // Then, generate random filters using the factory
        $this->createRandomFilters();
    }

    /**
     * Create specific demo filters with realistic examples.
     */
    protected function createDemoFilters(): void
    {
        // Replace type filters - obscure profanity
        WordFilter::updateOrCreate(
            ['pattern' => 'badword'],
            [
                'replacement' => '******',
                'filter_type' => 'replace',
                'pattern_type' => 'exact',
                'severity' => 'high',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages', 'signatures'],
                'notes' => 'Common profanity - exact match',
            ]
        );

        WordFilter::updateOrCreate(
            ['pattern' => 'damn'],
            [
                'replacement' => 'd***',
                'filter_type' => 'replace',
                'pattern_type' => 'exact',
                'severity' => 'low',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts'],
                'notes' => 'Mild profanity',
            ]
        );

        // Wildcard patterns
        WordFilter::updateOrCreate(
            ['pattern' => '*spam*'],
            [
                'replacement' => '[removed]',
                'filter_type' => 'replace',
                'pattern_type' => 'wildcard',
                'severity' => 'medium',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages'],
                'notes' => 'Spam-related content',
            ]
        );

        WordFilter::updateOrCreate(
            ['pattern' => 'hate*'],
            [
                'replacement' => '****',
                'filter_type' => 'replace',
                'pattern_type' => 'wildcard',
                'severity' => 'high',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages', 'usernames'],
                'notes' => 'Hate speech patterns',
            ]
        );

        // Regex patterns
        WordFilter::updateOrCreate(
            ['pattern' => '/\\b(viagra|cialis|levitra)\\b/i'],
            [
                'replacement' => '[pharmaceutical]',
                'filter_type' => 'replace',
                'pattern_type' => 'regex',
                'severity' => 'medium',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages', 'signatures'],
                'notes' => 'Pharmaceutical spam',
            ]
        );

        WordFilter::updateOrCreate(
            ['pattern' => '/\\b\\d{3}-\\d{3}-\\d{4}\\b/'],
            [
                'replacement' => '[phone number]',
                'filter_type' => 'replace',
                'pattern_type' => 'regex',
                'severity' => 'low',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages'],
                'notes' => 'Phone number pattern (US format)',
            ]
        );

        // Block type filters - completely prevent posting
        WordFilter::updateOrCreate(
            ['pattern' => 'blocked_domain.com'],
            [
                'replacement' => null,
                'filter_type' => 'block',
                'pattern_type' => 'exact',
                'severity' => 'high',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages', 'signatures'],
                'notes' => 'Known malicious domain',
            ]
        );

        WordFilter::updateOrCreate(
            ['pattern' => '*malware*'],
            [
                'replacement' => null,
                'filter_type' => 'block',
                'pattern_type' => 'wildcard',
                'severity' => 'high',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages'],
                'notes' => 'Malware-related content',
            ]
        );

        WordFilter::updateOrCreate(
            ['pattern' => '/\\b(torrent|magnet:|ed2k:)\\/\\/\\b/i'],
            [
                'replacement' => null,
                'filter_type' => 'block',
                'pattern_type' => 'regex',
                'severity' => 'medium',
                'is_active' => false, // Disabled by default for torrent sites
                'case_sensitive' => false,
                'applies_to' => ['posts'],
                'notes' => 'Torrent/P2P links - disabled for torrent trackers',
            ]
        );

        // Moderate type filters - flag for review
        WordFilter::updateOrCreate(
            ['pattern' => 'suspicious'],
            [
                'replacement' => null,
                'filter_type' => 'moderate',
                'pattern_type' => 'exact',
                'severity' => 'low',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts'],
                'notes' => 'Flag for manual review',
            ]
        );

        WordFilter::updateOrCreate(
            ['pattern' => '*scam*'],
            [
                'replacement' => null,
                'filter_type' => 'moderate',
                'pattern_type' => 'wildcard',
                'severity' => 'medium',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages'],
                'notes' => 'Potential scam content',
            ]
        );

        WordFilter::updateOrCreate(
            ['pattern' => '/\\$\\d{3,}/i'],
            [
                'replacement' => null,
                'filter_type' => 'moderate',
                'pattern_type' => 'regex',
                'severity' => 'low',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages'],
                'notes' => 'Large dollar amounts - potential scam',
            ]
        );

        // Email masking
        WordFilter::updateOrCreate(
            ['pattern' => '/([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+\\.[a-zA-Z]{2,})/i'],
            [
                'replacement' => '[email protected]',
                'filter_type' => 'replace',
                'pattern_type' => 'regex',
                'severity' => 'low',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts'],
                'notes' => 'Email address masking for privacy',
            ]
        );

        // URL shortener blocking
        WordFilter::updateOrCreate(
            ['pattern' => '/\\b(bit\\.ly|tinyurl\\.com|goo\\.gl|t\\.co)\\/\\w+/i'],
            [
                'replacement' => '[shortened URL]',
                'filter_type' => 'replace',
                'pattern_type' => 'regex',
                'severity' => 'medium',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages'],
                'notes' => 'URL shorteners - potential security risk',
            ]
        );

        // Inappropriate username filter
        WordFilter::updateOrCreate(
            ['pattern' => 'admin'],
            [
                'replacement' => null,
                'filter_type' => 'block',
                'pattern_type' => 'exact',
                'severity' => 'high',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['usernames'],
                'notes' => 'Prevent impersonation of administrators',
            ]
        );

        WordFilter::updateOrCreate(
            ['pattern' => '*moderator*'],
            [
                'replacement' => null,
                'filter_type' => 'block',
                'pattern_type' => 'wildcard',
                'severity' => 'high',
                'is_active' => true,
                'case_sensitive' => false,
                'applies_to' => ['usernames'],
                'notes' => 'Prevent impersonation of moderators',
            ]
        );

        // Case-sensitive filter example
        WordFilter::updateOrCreate(
            ['pattern' => 'CEO'],
            [
                'replacement' => '[title]',
                'filter_type' => 'replace',
                'pattern_type' => 'exact',
                'severity' => 'low',
                'is_active' => true,
                'case_sensitive' => true,
                'applies_to' => ['posts'],
                'notes' => 'Replace CEO when in all caps only',
            ]
        );

        // Multiple content type example
        WordFilter::updateOrCreate(
            ['pattern' => 'test123'],
            [
                'replacement' => '[test]',
                'filter_type' => 'replace',
                'pattern_type' => 'exact',
                'severity' => 'low',
                'is_active' => false, // Inactive example
                'case_sensitive' => false,
                'applies_to' => ['posts', 'private_messages', 'usernames', 'signatures', 'profile_fields'],
                'notes' => 'Test filter - currently disabled',
            ]
        );
    }

    /**
     * Create random filters using the factory.
     */
    protected function createRandomFilters(): void
    {
        // Create 10 random replace filters
        WordFilter::factory()
            ->count(10)
            ->create([
                'filter_type' => 'replace',
            ]);

        // Create 5 random block filters
        WordFilter::factory()
            ->count(5)
            ->create([
                'filter_type' => 'block',
                'replacement' => null,
            ]);

        // Create 5 random moderate filters
        WordFilter::factory()
            ->count(5)
            ->create([
                'filter_type' => 'moderate',
                'replacement' => null,
            ]);

        // Create some high severity filters
        WordFilter::factory()
            ->count(5)
            ->create([
                'severity' => 'high',
                'is_active' => true,
            ]);

        // Create some regex pattern filters
        WordFilter::factory()
            ->count(5)
            ->create([
                'pattern_type' => 'regex',
                'pattern' => $this->generateRandomRegexPattern(),
            ]);

        // Create some wildcard filters for spam detection
        WordFilter::factory()
            ->count(5)
            ->create([
                'pattern_type' => 'wildcard',
                'pattern' => '*' . fake()->randomElement(['buy', 'cheap', 'free', 'click', 'download']) . '*',
                'filter_type' => 'replace',
                'replacement' => '[SPAM]',
                'applies_to' => ['posts', 'private_messages'],
            ]);

        // Create inactive filters for testing
        WordFilter::factory()
            ->count(5)
            ->create([
                'is_active' => false,
            ]);
    }

    /**
     * Generate a random regex pattern for testing.
     */
    protected function generateRandomRegexPattern(): string
    {
        $patterns = [
            '/\\b\\d{3}-\\d{2}-\\d{4}\\b/', // SSN pattern
            '/\\b[A-Z]{2}\\d{6}\\b/', // License plate pattern
            '/\\b(https?:\\/\\/)?[\\w\\-]+(\\.[\\w\\-]+)+[/#?]?.*$/', // URL pattern
            '/\\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}\\b/i', // Email pattern
            '/\\b\\d{4}[\\s-]?\\d{4}[\\s-]?\\d{4}[\\s-]?\\d{4}\\b/', // Credit card pattern
            '/\\b(\\+?1[\\s-]?)?\\(?\\d{3}\\)?[\\s-]?\\d{3}[\\s-]?\\d{4}\\b/', // Phone pattern
            '/\\$\\d+(\\.\\d{2})?\\b/', // Currency pattern
            '/\\b[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}\\b/i', // UUID pattern
        ];

        return fake()->randomElement($patterns);
    }
}
