<?php

declare(strict_types=1);

/**
 * Integration tests for info_hash binary handling in SQL queries.
 *
 * Verifies that UNHEX(bin2hex()) approach correctly stores and retrieves
 * binary info_hash values, including edge cases that broke the old
 * rtrim(DB()->escape()) approach.
 *
 * Requires a real MySQL/MariaDB connection configured in .env
 */

// Use a dedicated test topic_id range to avoid conflicts
const TEST_TOPIC_ID_BASE = 9999000;

beforeAll(function () {
    // Load .env
    $envFile = __DIR__ . '/../../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                putenv(trim($line));
            }
        }
    }
});

beforeEach(function () {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_DATABASE') ?: 'tp';
    $user = getenv('DB_USERNAME') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: '';

    try {
        $this->pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    } catch (PDOException $e) {
        $this->markTestSkipped('MySQL connection not available: ' . $e->getMessage());
    }

    // Clean up test rows from previous runs
    $this->pdo->exec('DELETE FROM bb_bt_torrents WHERE topic_id >= ' . TEST_TOPIC_ID_BASE . ' AND topic_id < ' . (TEST_TOPIC_ID_BASE + 1000));
});

afterEach(function () {
    if (isset($this->pdo)) {
        $this->pdo->exec('DELETE FROM bb_bt_torrents WHERE topic_id >= ' . TEST_TOPIC_ID_BASE . ' AND topic_id < ' . (TEST_TOPIC_ID_BASE + 1000));
        $this->pdo = null;
    }
});

/**
 * Helper: insert a test torrent row with given binary info_hash
 */
function insertTestTorrent(PDO $pdo, int $topicId, ?string $infoHash, ?string $infoHashV2 = null): void
{
    $stmt = $pdo->prepare('
        INSERT INTO bb_bt_torrents (topic_id, info_hash, info_hash_v2, poster_id, forum_id, size, reg_time)
        VALUES (:topic_id, :info_hash, :info_hash_v2, 0, 0, 0, 0)
    ');
    $stmt->execute([
        'topic_id' => $topicId,
        'info_hash' => $infoHash,
        'info_hash_v2' => $infoHashV2,
    ]);
}

/**
 * Helper: look up torrent using the new UNHEX approach (as in AnnounceController)
 */
function findByUnhex(PDO $pdo, string $infoHashHex): ?array
{
    $sql = "SELECT topic_id, HEX(info_hash) as info_hash_hex, HEX(info_hash_v2) as info_hash_v2_hex
            FROM bb_bt_torrents
            WHERE info_hash = UNHEX('{$infoHashHex}')
               OR SUBSTRING(info_hash_v2, 1, 20) = UNHEX('{$infoHashHex}')
            LIMIT 1";
    $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/**
 * Helper: look up torrent using the OLD rtrim(escape()) approach
 */
function findByOldEscape(PDO $pdo, string $rawInfoHash): ?array
{
    $escaped = rtrim($pdo->quote($rawInfoHash), ' ');
    // PDO::quote adds surrounding quotes, strip them for interpolation
    $escaped = substr($escaped, 1, -1);
    $escaped = rtrim($escaped, ' ');

    $sql = "SELECT topic_id, HEX(info_hash) as info_hash_hex
            FROM bb_bt_torrents
            WHERE info_hash = '{$escaped}'
            LIMIT 1";
    $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

// ─── Tests ───

describe('UNHEX() function availability', function () {
    it('works in the current database engine', function () {
        $result = $this->pdo->query("SELECT UNHEX('48454C4C4F') as val")->fetch(PDO::FETCH_ASSOC);
        expect($result['val'])->toBe('HELLO');
    });

    it('UNHEX(HEX(x)) is identity for binary data', function () {
        $binary = random_bytes(20);
        $hex = bin2hex($binary);
        $result = $this->pdo->query("SELECT UNHEX('{$hex}') as val")->fetch(PDO::FETCH_ASSOC);
        expect($result['val'])->toBe($binary);
    });
});

describe('Normal info_hash lookup via UNHEX', function () {
    it('finds a torrent by v1 info_hash', function () {
        $hash = random_bytes(20);
        $topicId = TEST_TOPIC_ID_BASE + 1;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });

    it('finds a torrent by truncated v2 info_hash', function () {
        $hashV2 = random_bytes(32);
        $topicId = TEST_TOPIC_ID_BASE + 2;

        insertTestTorrent($this->pdo, $topicId, null, $hashV2);

        // Client sends first 20 bytes of v2 hash
        $truncated = substr($hashV2, 0, 20);
        $found = findByUnhex($this->pdo, bin2hex($truncated));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });
});

describe('Edge case: trailing space byte (0x20)', function () {
    it('UNHEX finds hash ending with 0x20', function () {
        // This is the hash that rtrim() would corrupt
        $hash = random_bytes(19) . "\x20";
        $topicId = TEST_TOPIC_ID_BASE + 10;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });

    it('UNHEX finds hash ending with multiple 0x20', function () {
        $hash = random_bytes(17) . "\x20\x20\x20";
        $topicId = TEST_TOPIC_ID_BASE + 11;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });

    it('old rtrim approach FAILS on hash ending with 0x20', function () {
        $hash = random_bytes(19) . "\x20";
        $topicId = TEST_TOPIC_ID_BASE + 12;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByOldEscape($this->pdo, $hash);

        // Old approach strips trailing 0x20, so it should NOT find the torrent
        expect($found)->toBeNull();
    });
});

describe('Edge case: single quote byte (0x27)', function () {
    it('UNHEX finds hash containing 0x27', function () {
        // 0x27 = single quote, PDO::quote would escape this
        $hash = random_bytes(10) . "\x27" . random_bytes(9);
        $topicId = TEST_TOPIC_ID_BASE + 20;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });
});

describe('Edge case: backslash byte (0x5C)', function () {
    it('UNHEX finds hash containing 0x5C', function () {
        $hash = random_bytes(10) . "\x5c" . random_bytes(9);
        $topicId = TEST_TOPIC_ID_BASE + 30;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });
});

describe('Edge case: null byte (0x00)', function () {
    it('UNHEX finds hash containing 0x00', function () {
        $hash = random_bytes(10) . "\x00" . random_bytes(9);
        $topicId = TEST_TOPIC_ID_BASE + 40;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });

    it('UNHEX finds hash starting with 0x00', function () {
        $hash = "\x00" . random_bytes(19);
        $topicId = TEST_TOPIC_ID_BASE + 41;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });

    it('UNHEX finds hash ending with 0x00', function () {
        $hash = random_bytes(19) . "\x00";
        $topicId = TEST_TOPIC_ID_BASE + 42;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });
});

describe('Edge case: worst-case binary patterns', function () {
    it('UNHEX finds all-zeros hash', function () {
        $hash = str_repeat("\x00", 20);
        $topicId = TEST_TOPIC_ID_BASE + 50;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });

    it('UNHEX finds all-0xFF hash', function () {
        $hash = str_repeat("\xff", 20);
        $topicId = TEST_TOPIC_ID_BASE + 51;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });

    it('UNHEX finds all-spaces hash', function () {
        $hash = str_repeat("\x20", 20);
        $topicId = TEST_TOPIC_ID_BASE + 52;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });

    it('UNHEX finds hash with backslash-quote sequence', function () {
        // \' sequence = 0x5c 0x27 — the exact pattern DB()->escape() would mangle
        $hash = random_bytes(8) . "\x5c\x27" . random_bytes(10);
        $topicId = TEST_TOPIC_ID_BASE + 53;

        insertTestTorrent($this->pdo, $topicId, $hash);
        $found = findByUnhex($this->pdo, bin2hex($hash));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });
});

describe('Edge case: v2 hash (32 bytes)', function () {
    it('UNHEX finds full v2 hash', function () {
        $hashV2 = random_bytes(32);
        $hashV2Hex = bin2hex($hashV2);
        $topicId = TEST_TOPIC_ID_BASE + 60;

        insertTestTorrent($this->pdo, $topicId, null, $hashV2);

        $sql = "SELECT topic_id FROM bb_bt_torrents WHERE info_hash_v2 = UNHEX('{$hashV2Hex}') LIMIT 1";
        $row = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

        expect($row)->not->toBeNull()
            ->and((int)$row['topic_id'])->toBe($topicId);
    });

    it('UNHEX finds v2 hash with problematic bytes', function () {
        // v2 hash with null, space, quote, backslash all present
        $hashV2 = "\x00\x20\x27\x5c" . random_bytes(28);
        $hashV2Hex = bin2hex($hashV2);
        $topicId = TEST_TOPIC_ID_BASE + 61;

        insertTestTorrent($this->pdo, $topicId, null, $hashV2);

        $sql = "SELECT topic_id FROM bb_bt_torrents WHERE info_hash_v2 = UNHEX('{$hashV2Hex}') LIMIT 1";
        $row = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

        expect($row)->not->toBeNull()
            ->and((int)$row['topic_id'])->toBe($topicId);
    });
});

describe('Hybrid torrent lookup (v1 + v2)', function () {
    it('finds hybrid torrent by v1 hash', function () {
        $hashV1 = random_bytes(20);
        $hashV2 = random_bytes(32);
        $topicId = TEST_TOPIC_ID_BASE + 70;

        insertTestTorrent($this->pdo, $topicId, $hashV1, $hashV2);
        $found = findByUnhex($this->pdo, bin2hex($hashV1));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });

    it('finds hybrid torrent by truncated v2 hash', function () {
        $hashV1 = random_bytes(20);
        $hashV2 = random_bytes(32);
        $topicId = TEST_TOPIC_ID_BASE + 71;

        insertTestTorrent($this->pdo, $topicId, $hashV1, $hashV2);

        $truncatedV2 = substr($hashV2, 0, 20);
        $found = findByUnhex($this->pdo, bin2hex($truncatedV2));

        expect($found)->not->toBeNull()
            ->and((int)$found['topic_id'])->toBe($topicId);
    });
});

describe('Distinct hashes are not confused', function () {
    it('different hashes return different torrents', function () {
        $hash1 = random_bytes(20);
        $hash2 = random_bytes(20);
        $topicId1 = TEST_TOPIC_ID_BASE + 80;
        $topicId2 = TEST_TOPIC_ID_BASE + 81;

        insertTestTorrent($this->pdo, $topicId1, $hash1);
        insertTestTorrent($this->pdo, $topicId2, $hash2);

        $found1 = findByUnhex($this->pdo, bin2hex($hash1));
        $found2 = findByUnhex($this->pdo, bin2hex($hash2));

        expect((int)$found1['topic_id'])->toBe($topicId1)
            ->and((int)$found2['topic_id'])->toBe($topicId2);
    });

    it('hashes differing only in trailing byte are distinct', function () {
        $prefix = random_bytes(19);
        $hash1 = $prefix . "\x20"; // space
        $hash2 = $prefix . "\x21"; // exclamation mark
        $topicId1 = TEST_TOPIC_ID_BASE + 82;
        $topicId2 = TEST_TOPIC_ID_BASE + 83;

        insertTestTorrent($this->pdo, $topicId1, $hash1);
        insertTestTorrent($this->pdo, $topicId2, $hash2);

        $found1 = findByUnhex($this->pdo, bin2hex($hash1));
        $found2 = findByUnhex($this->pdo, bin2hex($hash2));

        expect((int)$found1['topic_id'])->toBe($topicId1)
            ->and((int)$found2['topic_id'])->toBe($topicId2);
    });
});

describe('Scrape IN() clause with UNHEX', function () {
    it('finds multiple torrents via IN(UNHEX(), UNHEX())', function () {
        $hashes = [];
        for ($i = 0; $i < 5; $i++) {
            $hash = random_bytes(20);
            $topicId = TEST_TOPIC_ID_BASE + 90 + $i;
            insertTestTorrent($this->pdo, $topicId, $hash);
            $hashes[] = bin2hex($hash);
        }

        $unhexList = implode("'), UNHEX('", $hashes);
        $sql = "SELECT topic_id FROM bb_bt_torrents
                WHERE info_hash IN (UNHEX('{$unhexList}'))
                ORDER BY topic_id";
        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        expect($rows)->toHaveCount(5);
        for ($i = 0; $i < 5; $i++) {
            expect((int)$rows[$i]['topic_id'])->toBe(TEST_TOPIC_ID_BASE + 90 + $i);
        }
    });

    it('IN clause with problematic hashes works', function () {
        $edgeCases = [
            random_bytes(19) . "\x20",        // trailing space
            random_bytes(19) . "\x00",        // trailing null
            "\x27" . random_bytes(19),         // leading quote
            "\x5c\x27" . random_bytes(18),    // backslash-quote
            str_repeat("\x00", 20),            // all zeros
        ];

        $hashes = [];
        foreach ($edgeCases as $i => $hash) {
            $topicId = TEST_TOPIC_ID_BASE + 100 + $i;
            insertTestTorrent($this->pdo, $topicId, $hash);
            $hashes[] = bin2hex($hash);
        }

        $unhexList = implode("'), UNHEX('", $hashes);
        $sql = "SELECT topic_id FROM bb_bt_torrents
                WHERE info_hash IN (UNHEX('{$unhexList}'))
                ORDER BY topic_id";
        $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        expect($rows)->toHaveCount(5);
    });
});
