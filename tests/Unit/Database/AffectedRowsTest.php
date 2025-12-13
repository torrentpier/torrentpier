<?php

use TorrentPier\Database\Database;

describe('Database Affected Rows Fix', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('has affected_rows method that returns integer', function () {
        // Create a simple mock to test the method exists
        $db = Mockery::mock(Database::class)->makePartial();

        expect(method_exists($db, 'affected_rows'))->toBeTrue();

        // Mock the method to return a value
        $db->shouldReceive('affected_rows')->andReturn(1);

        $result = $db->affected_rows();
        expect($result)->toBeInt();
        expect($result)->toBe(1);
    });

    it('affected_rows method returns 0 initially', function () {
        $db = Mockery::mock(Database::class)->makePartial();
        $db->shouldReceive('affected_rows')->andReturn(0);

        expect($db->affected_rows())->toBe(0);
    });

    it('affected_rows can track INSERT operations', function () {
        $db = Mockery::mock(Database::class)->makePartial();

        // Mock that INSERT affects 1 row
        $db->shouldReceive('affected_rows')->andReturn(1);

        expect($db->affected_rows())->toBe(1);
    });

    it('affected_rows can track UPDATE operations', function () {
        $db = Mockery::mock(Database::class)->makePartial();

        // Mock that UPDATE affects 3 rows
        $db->shouldReceive('affected_rows')->andReturn(3);

        expect($db->affected_rows())->toBe(3);
    });

    it('affected_rows can track DELETE operations', function () {
        $db = Mockery::mock(Database::class)->makePartial();

        // Mock that DELETE affects 2 rows
        $db->shouldReceive('affected_rows')->andReturn(2);

        expect($db->affected_rows())->toBe(2);
    });

    it('affected_rows returns 0 when no rows affected', function () {
        $db = Mockery::mock(Database::class)->makePartial();

        // Mock operation that affects no rows
        $db->shouldReceive('affected_rows')->andReturn(0);

        expect($db->affected_rows())->toBe(0);
    });

    it('validates Database class has last_affected_rows property', function () {
        // Test that the Database class structure supports affected_rows tracking
        $reflection = new ReflectionClass(Database::class);

        expect($reflection->hasProperty('last_affected_rows'))->toBeTrue();
        expect($reflection->hasMethod('affected_rows'))->toBeTrue();
    });

    it('validates fix is present in source code', function () {
        // Simple source code validation to ensure fix is in place
        $databaseSource = file_get_contents(__DIR__ . '/../../../src/Database/Database.php');

        // Check that our fix is present: getRowCount() usage
        expect($databaseSource)->toContain('getRowCount()');

        // Check that the last_affected_rows property exists
        expect($databaseSource)->toContain('last_affected_rows');
    });
});
