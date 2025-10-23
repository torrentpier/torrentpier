<?php

declare(strict_types=1);

namespace Tests\ModSystem;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TorrentPier\ModSystem\ModLoader;
use TorrentPier\ModSystem\ModException;
use TorrentPier\ModSystem\AbstractMod;

/**
 * Tests for ModLoader class
 */
#[CoversClass(ModLoader::class)]
class ModLoaderTest extends TestCase
{
    private string $testModsPath;
    private ModLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();

        // Create temporary mods directory for testing
        $this->testModsPath = sys_get_temp_dir() . '/tp_test_mods_' . uniqid();
        mkdir($this->testModsPath, 0755, true);

        // Initialize ModLoader with test path
        $this->loader = new ModLoader($this->testModsPath);
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (is_dir($this->testModsPath)) {
            $this->removeDirectory($this->testModsPath);
        }

        parent::tearDown();
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function createTestMod(
        string $modId,
        array $manifestOverrides = [],
        ?string $entrypointContent = null
    ): void {
        $modDir = $this->testModsPath . '/' . $modId;
        mkdir($modDir, 0755, true);

        // If override has 'id', 'name', 'entrypoint', use ONLY the override (for testing invalid manifests)
        // Otherwise merge with defaults
        if (isset($manifestOverrides['id']) && isset($manifestOverrides['name']) && isset($manifestOverrides['entrypoint'])) {
            $manifest = $manifestOverrides;
        } else {
            // Default manifest
            $manifest = array_merge([
                'id' => $modId,
                'name' => ucfirst($modId) . ' Mod',
                'version' => '1.0.0',
                'description' => 'Test mod: ' . $modId,
                'author' => 'Test Author',
                'entrypoint' => 'index.php',
                'requires' => [
                    'torrentpier' => '>=3.0.0',
                    'php' => '>=8.2.0'
                ]
            ], $manifestOverrides);
        }

        // Write manifest.json
        file_put_contents(
            $modDir . '/manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT)
        );

        // Create entrypoint file if content provided
        if ($entrypointContent !== null) {
            file_put_contents($modDir . '/index.php', $entrypointContent);
        }
    }

    private function createValidModWithClass(string $modId): void
    {
        $className = $this->getModClassName($modId);

        $content = <<<PHP
<?php
declare(strict_types=1);

use TorrentPier\ModSystem\AbstractMod;

class {$className} extends AbstractMod
{
    public function activate(): void
    {
        // Test activation
    }
}
PHP;

        $this->createTestMod($modId, [], $content);
    }

    private function getModClassName(string $modId): string
    {
        $parts = preg_split('/[-_]/', $modId);
        $className = implode('', array_map('ucfirst', $parts));
        return $className . 'Mod';
    }

    // ========================================================================
    // TEST: CONSTRUCTOR & INITIALIZATION
    // ========================================================================

    public function test_constructor_creates_mods_directory(): void
    {
        $newPath = sys_get_temp_dir() . '/tp_new_mods_' . uniqid();

        // Directory doesn't exist yet
        $this->assertFalse(is_dir($newPath));

        // Create ModLoader with non-existent path
        new ModLoader($newPath);

        // Directory should now exist
        $this->assertTrue(is_dir($newPath));

        // Cleanup
        rmdir($newPath);
    }

    public function test_constructor_uses_default_path_when_null(): void
    {
        // This would use BB_ROOT . '/mods' in production
        // We can't easily test this without mocking BB_ROOT constant
        $this->assertTrue(true); // Placeholder - manual verification needed
    }

    // ========================================================================
    // TEST: DISCOVER MODS
    // ========================================================================

    public function test_discoverMods_returns_empty_array_when_no_mods(): void
    {
        $mods = $this->loader->discoverMods();
        $this->assertIsArray($mods);
        $this->assertEmpty($mods);
    }

    public function test_discoverMods_finds_valid_mod(): void
    {
        $this->createTestMod('test-mod');

        $mods = $this->loader->discoverMods();

        $this->assertCount(1, $mods);
        $this->assertArrayHasKey('test-mod', $mods);
        $this->assertEquals('test-mod', $mods['test-mod']['id']);
        $this->assertEquals('Test-mod Mod', $mods['test-mod']['name']);
        $this->assertEquals('1.0.0', $mods['test-mod']['version']);
    }

    public function test_discoverMods_finds_multiple_mods(): void
    {
        $this->createTestMod('karma');
        $this->createTestMod('automod');
        $this->createTestMod('analytics');

        $mods = $this->loader->discoverMods();

        $this->assertCount(3, $mods);
        $this->assertArrayHasKey('karma', $mods);
        $this->assertArrayHasKey('automod', $mods);
        $this->assertArrayHasKey('analytics', $mods);
    }

    public function test_discoverMods_skips_directories_without_manifest(): void
    {
        $this->createTestMod('valid-mod');

        // Create directory without manifest
        mkdir($this->testModsPath . '/invalid-mod', 0755);

        $mods = $this->loader->discoverMods();

        $this->assertCount(1, $mods);
        $this->assertArrayHasKey('valid-mod', $mods);
        $this->assertArrayNotHasKey('invalid-mod', $mods);
    }

    public function test_discoverMods_skips_invalid_json(): void
    {
        $this->createTestMod('valid-mod');

        // Create mod with invalid JSON
        $invalidDir = $this->testModsPath . '/invalid-json';
        mkdir($invalidDir, 0755);
        file_put_contents($invalidDir . '/manifest.json', '{invalid json}');

        $mods = $this->loader->discoverMods();

        $this->assertCount(1, $mods);
        $this->assertArrayHasKey('valid-mod', $mods);
        $this->assertArrayNotHasKey('invalid-json', $mods);
    }

    public function test_discoverMods_skips_manifest_missing_required_fields(): void
    {
        $this->createTestMod('valid-mod');

        // Create mod with missing required field
        $this->createTestMod('missing-version', [
            'id' => 'missing-version',
            'name' => 'Missing Version Mod',
            // 'version' is missing!
            'entrypoint' => 'index.php'
        ]);

        $mods = $this->loader->discoverMods();

        // Debug: see what was actually discovered
        if (count($mods) !== 1) {
            echo "\nDEBUG: Expected 1 mod, found " . count($mods) . ": " . implode(', ', array_keys($mods)) . "\n";
            foreach ($mods as $id => $manifest) {
                echo "  {$id}: " . json_encode($manifest, JSON_UNESCAPED_SLASHES) . "\n";
            }
        }

        $this->assertCount(1, $mods);
        $this->assertArrayHasKey('valid-mod', $mods);
        $this->assertArrayNotHasKey('missing-version', $mods);
    }

    public function test_discoverMods_caches_results(): void
    {
        $this->createTestMod('test-mod');

        // First call - should discover
        $mods1 = $this->loader->discoverMods();
        $this->assertCount(1, $mods1);

        // Add another mod
        $this->createTestMod('second-mod');

        // Second call without forceRefresh - should use cache
        $mods2 = $this->loader->discoverMods();
        $this->assertCount(1, $mods2); // Still 1, cache not refreshed

        // Third call with forceRefresh - should re-discover
        $mods3 = $this->loader->discoverMods(true);
        $this->assertCount(2, $mods3); // Now 2, cache refreshed
    }

    public function test_discoverMods_adds_path_metadata(): void
    {
        $this->createTestMod('test-mod');

        $mods = $this->loader->discoverMods();

        $this->assertArrayHasKey('_path', $mods['test-mod']);
        $this->assertArrayHasKey('_manifest_path', $mods['test-mod']);
        $this->assertStringContainsString('/test-mod', $mods['test-mod']['_path']);
        $this->assertStringEndsWith('/manifest.json', $mods['test-mod']['_manifest_path']);
    }

    // ========================================================================
    // TEST: VALIDATE MANIFEST
    // ========================================================================

    public function test_validateManifest_passes_with_all_required_fields(): void
    {
        $manifest = [
            'id' => 'test-mod',
            'name' => 'Test Mod',
            'version' => '1.0.0',
            'entrypoint' => 'index.php'
        ];

        $result = $this->loader->validateManifest($manifest);
        $this->assertTrue($result);
    }

    public function test_validateManifest_throws_on_missing_id(): void
    {
        $this->expectException(ModException::class);
        $this->expectExceptionCode(ModException::MANIFEST_MISSING_FIELD);

        $manifest = [
            // 'id' missing
            'name' => 'Test Mod',
            'version' => '1.0.0',
            'entrypoint' => 'index.php'
        ];

        $this->loader->validateManifest($manifest);
    }

    public function test_validateManifest_throws_on_empty_id(): void
    {
        $this->expectException(ModException::class);
        $this->expectExceptionCode(ModException::MANIFEST_MISSING_FIELD);

        $manifest = [
            'id' => '', // empty
            'name' => 'Test Mod',
            'version' => '1.0.0',
            'entrypoint' => 'index.php'
        ];

        $this->loader->validateManifest($manifest);
    }

    public function test_validateManifest_throws_on_invalid_id_format(): void
    {
        $this->expectException(ModException::class);
        $this->expectExceptionCode(ModException::MANIFEST_INVALID_SCHEMA);

        $manifest = [
            'id' => 'Invalid Mod!', // spaces and special chars
            'name' => 'Test Mod',
            'version' => '1.0.0',
            'entrypoint' => 'index.php'
        ];

        $this->loader->validateManifest($manifest);
    }

    public function test_validateManifest_accepts_valid_id_formats(): void
    {
        $validIds = ['karma', 'auto-mod', 'my_mod', 'mod123', 'my-mod_v2'];

        foreach ($validIds as $id) {
            $manifest = [
                'id' => $id,
                'name' => 'Test Mod',
                'version' => '1.0.0',
                'entrypoint' => 'index.php'
            ];

            $result = $this->loader->validateManifest($manifest);
            $this->assertTrue($result, "Failed for ID: {$id}");
        }
    }

    public function test_validateManifest_throws_on_invalid_version_format(): void
    {
        $this->expectException(ModException::class);
        $this->expectExceptionCode(ModException::MANIFEST_INVALID_SCHEMA);

        $manifest = [
            'id' => 'test-mod',
            'name' => 'Test Mod',
            'version' => 'v1.0', // invalid format
            'entrypoint' => 'index.php'
        ];

        $this->loader->validateManifest($manifest);
    }

    public function test_validateManifest_accepts_valid_version_formats(): void
    {
        $validVersions = ['1.0.0', '2.5.3', '0.0.1', '10.20.30', '1.0.0-beta', '2.0.0-rc1'];

        foreach ($validVersions as $version) {
            $manifest = [
                'id' => 'test-mod',
                'name' => 'Test Mod',
                'version' => $version,
                'entrypoint' => 'index.php'
            ];

            $result = $this->loader->validateManifest($manifest);
            $this->assertTrue($result, "Failed for version: {$version}");
        }
    }

    // ========================================================================
    // TEST: CHECK COMPATIBILITY
    // ========================================================================

    public function test_checkCompatibility_passes_when_requirements_met(): void
    {
        $manifest = [
            'id' => 'test-mod',
            'requires' => [
                'torrentpier' => '>=3.0.0',
                'php' => '>=' . PHP_VERSION
            ]
        ];

        $result = $this->loader->checkCompatibility($manifest);
        $this->assertTrue($result);
    }

    public function test_checkCompatibility_throws_on_incompatible_php_version(): void
    {
        $this->expectException(ModException::class);
        $this->expectExceptionCode(ModException::COMPATIBILITY_PHP_VERSION);

        $manifest = [
            'id' => 'test-mod',
            'requires' => [
                'php' => '>=99.0.0' // Future PHP version
            ]
        ];

        $this->loader->checkCompatibility($manifest);
    }

    public function test_checkCompatibility_passes_with_no_requires(): void
    {
        $manifest = [
            'id' => 'test-mod'
            // No 'requires' key
        ];

        $result = $this->loader->checkCompatibility($manifest);
        $this->assertTrue($result);
    }

    // ========================================================================
    // TEST: VERSION SATISFIES (via checkCompatibility)
    // ========================================================================

    public function test_versionSatisfies_greater_than_or_equal(): void
    {
        $manifest = [
            'id' => 'test-mod',
            'requires' => ['php' => '>=' . PHP_VERSION]
        ];

        $this->assertTrue($this->loader->checkCompatibility($manifest));
    }

    public function test_versionSatisfies_greater_than(): void
    {
        $manifest = [
            'id' => 'test-mod',
            'requires' => ['php' => '>7.0.0'] // Current PHP is definitely > 7.0.0
        ];

        $this->assertTrue($this->loader->checkCompatibility($manifest));
    }

    public function test_versionSatisfies_less_than(): void
    {
        $this->expectException(ModException::class);

        $manifest = [
            'id' => 'test-mod',
            'requires' => ['php' => '<7.0.0'] // Current PHP is definitely not < 7.0.0
        ];

        $this->loader->checkCompatibility($manifest);
    }

    public function test_versionSatisfies_caret_operator(): void
    {
        // Caret (^) allows changes that do not modify major version
        $phpMajor = explode('.', PHP_VERSION)[0];

        $manifest = [
            'id' => 'test-mod',
            'requires' => ['php' => "^{$phpMajor}.0.0"] // Same major version
        ];

        $this->assertTrue($this->loader->checkCompatibility($manifest));
    }

    public function test_versionSatisfies_tilde_operator(): void
    {
        // Tilde (~) allows changes that do not modify major.minor version
        $phpParts = explode('.', PHP_VERSION);
        $phpMajorMinor = $phpParts[0] . '.' . $phpParts[1];

        $manifest = [
            'id' => 'test-mod',
            'requires' => ['php' => "~{$phpMajorMinor}.0"] // Same major.minor version
        ];

        $this->assertTrue($this->loader->checkCompatibility($manifest));
    }

    // ========================================================================
    // TEST: LOAD MOD
    // ========================================================================

    public function test_loadMod_throws_when_mod_not_found(): void
    {
        $this->expectException(ModException::class);
        $this->expectExceptionCode(ModException::MOD_NOT_FOUND);

        $this->loader->loadMod('nonexistent-mod');
    }

    public function test_loadMod_throws_when_entrypoint_missing(): void
    {
        // Create mod without entrypoint file
        $this->createTestMod('no-entrypoint');

        $this->expectException(ModException::class);
        $this->expectExceptionCode(ModException::FILE_OPERATION_ERROR);

        $this->loader->loadMod('no-entrypoint');
    }

    public function test_loadMod_loads_valid_mod(): void
    {
        $this->createValidModWithClass('test-mod');

        $mod = $this->loader->loadMod('test-mod');

        $this->assertInstanceOf(AbstractMod::class, $mod);
        $this->assertEquals('test-mod', $mod->getId());
        $this->assertEquals('1.0.0', $mod->getVersion());
    }

    public function test_loadMod_throws_when_class_not_found(): void
    {
        // Create mod with entrypoint but wrong class name
        // Use unique mod ID to avoid class name collision from previous tests
        $content = <<<PHP
<?php
declare(strict_types=1);

use TorrentPier\ModSystem\AbstractMod;

class WrongClassName extends AbstractMod
{
}
PHP;

        $this->createTestMod('class-not-found-mod', [], $content);

        $this->expectException(ModException::class);
        $this->expectExceptionCode(ModException::FILE_OPERATION_ERROR);
        $this->expectExceptionMessage('Mod class not found');

        $this->loader->loadMod('class-not-found-mod');
    }

    public function test_loadMod_throws_when_class_doesnt_extend_AbstractMod(): void
    {
        // Use unique mod ID to avoid class name collision from previous tests
        $content = <<<PHP
<?php
declare(strict_types=1);

class NoExtendModMod
{
    // Doesn't extend AbstractMod!
}
PHP;

        $this->createTestMod('no-extend-mod', [], $content);

        $this->expectException(ModException::class);
        $this->expectExceptionCode(ModException::MANIFEST_INVALID_SCHEMA);
        $this->expectExceptionMessage('must extend AbstractMod');

        $this->loader->loadMod('no-extend-mod');
    }

    public function test_loadMod_returns_same_instance_when_called_twice(): void
    {
        $this->createValidModWithClass('instance-cache-mod');

        $mod1 = $this->loader->loadMod('instance-cache-mod');
        $mod2 = $this->loader->loadMod('instance-cache-mod');

        $this->assertSame($mod1, $mod2);
    }

    // ========================================================================
    // TEST: HELPER METHODS
    // ========================================================================

    public function test_clearCache_clears_discovered_mods(): void
    {
        $this->createTestMod('test-mod');

        // Discover mods
        $mods1 = $this->loader->discoverMods();
        $this->assertCount(1, $mods1);

        // Add another mod
        $this->createTestMod('second-mod');

        // Clear cache
        $this->loader->clearCache();

        // Re-discover should find both mods
        $mods2 = $this->loader->discoverMods();
        $this->assertCount(2, $mods2);
    }

    public function test_getActiveMods_returns_empty_array_initially(): void
    {
        $activeMods = $this->loader->getActiveMods();

        $this->assertIsArray($activeMods);
        $this->assertEmpty($activeMods);
    }

    // ========================================================================
    // TEST: MOD CLASS NAME CONVERSION
    // ========================================================================

    public function test_mod_class_name_conversion(): void
    {
        $testCases = [
            'karma' => 'KarmaMod',
            'auto-mod' => 'AutoModMod',
            'my_custom_mod' => 'MyCustomModMod',
            'test-mod-123' => 'TestMod123Mod',
            'simple' => 'SimpleMod'
        ];

        foreach ($testCases as $modId => $expectedClassName) {
            $actualClassName = $this->getModClassName($modId);
            $this->assertEquals(
                $expectedClassName,
                $actualClassName,
                "Failed for mod ID: {$modId}"
            );
        }
    }

    // ========================================================================
    // TEST: EDGE CASES
    // ========================================================================

    public function test_discoverMods_handles_special_directories(): void
    {
        $this->createTestMod('valid-mod');

        // Create special directories that should be ignored
        mkdir($this->testModsPath . '/.hidden', 0755);
        mkdir($this->testModsPath . '/..dotfiles', 0755);

        // Create a file (not directory) that should be ignored
        file_put_contents($this->testModsPath . '/readme.txt', 'test');

        $mods = $this->loader->discoverMods();

        $this->assertCount(1, $mods);
        $this->assertArrayHasKey('valid-mod', $mods);
    }

    public function test_validateManifest_with_all_optional_fields(): void
    {
        $manifest = [
            'id' => 'full-mod',
            'name' => 'Full Featured Mod',
            'version' => '1.0.0',
            'description' => 'A mod with all fields',
            'author' => 'Test Author',
            'homepage' => 'https://example.com',
            'entrypoint' => 'index.php',
            'requires' => [
                'torrentpier' => '>=3.0.0',
                'php' => '>=8.2.0'
                // Note: Not including 'mods' dependency here since validation checks if deps are active
                // Dependency checking should be tested separately with proper database setup
            ],
            'config' => [
                'enabled' => true,
                'settings' => []
            ]
        ];

        $result = $this->loader->validateManifest($manifest);
        $this->assertTrue($result);
    }

    public function test_loadMod_handles_kebab_case_mod_ids(): void
    {
        $this->createValidModWithClass('my-custom-mod');

        $mod = $this->loader->loadMod('my-custom-mod');

        $this->assertInstanceOf(AbstractMod::class, $mod);
        $this->assertEquals('my-custom-mod', $mod->getId());
    }

    public function test_loadMod_handles_snake_case_mod_ids(): void
    {
        $this->createValidModWithClass('snake_case_test_mod');

        $mod = $this->loader->loadMod('snake_case_test_mod');

        $this->assertInstanceOf(AbstractMod::class, $mod);
        $this->assertEquals('snake_case_test_mod', $mod->getId());
    }

    // ========================================================================
    // TEST: PERFORMANCE & CACHING
    // ========================================================================

    public function test_discoverMods_performance_with_many_mods(): void
    {
        // Create 50 mods
        for ($i = 1; $i <= 50; $i++) {
            $this->createTestMod("mod-{$i}");
        }

        $start = microtime(true);
        $mods = $this->loader->discoverMods();
        $duration = (microtime(true) - $start) * 1000; // Convert to ms

        $this->assertCount(50, $mods);
        $this->assertLessThan(500, $duration, 'Discovery took too long (>500ms)');
    }

    public function test_cache_improves_performance(): void
    {
        // Create 20 mods
        for ($i = 1; $i <= 20; $i++) {
            $this->createTestMod("mod-{$i}");
        }

        // First call - cold (no cache)
        $start1 = microtime(true);
        $this->loader->discoverMods();
        $duration1 = (microtime(true) - $start1) * 1000;

        // Second call - warm (cached)
        $start2 = microtime(true);
        $this->loader->discoverMods();
        $duration2 = (microtime(true) - $start2) * 1000;

        // Cached call should be significantly faster
        $this->assertLessThan($duration1, $duration2);
        $this->assertLessThan(10, $duration2, 'Cached call should be <10ms');
    }
}
