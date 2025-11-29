<?php
/**
 * Test All IF Patterns from Templates
 *
 * Run: php test_all_patterns.php
 * Tests real patterns from the TorrentPier templates
 */

require_once __DIR__ . '/vendor/autoload.php';

use TorrentPier\Template\Extensions\LegacySyntaxExtension;

$converter = new LegacySyntaxExtension();

// Real patterns from templates that might be problematic
$patterns = [
    // Simple variables
    '<!-- IF SHOW_FORUMS -->content<!-- ENDIF -->',
    '<!-- IF !IS_GUEST -->content<!-- ENDIF -->',
    '<!-- IF not IS_GUEST -->content<!-- ENDIF -->',

    // Constants
    '<!-- IF #IN_DEMO_MODE -->content<!-- ENDIF -->',
    '<!-- IF #RATIO_ENABLED -->content<!-- ENDIF -->',

    // PHP config access
    '<!-- IF $bb_cfg[\'allow_change\'][\'language\'] -->content<!-- ENDIF -->',
    '<!-- IF $bb_cfg[\'tracker\'][\'gold_silver_enabled\'] -->content<!-- ENDIF -->',

    // Combined conditions
    '<!-- IF $bb_cfg[\'invites_system\'][\'enabled\'] and not EDIT_PROFILE -->content<!-- ENDIF -->',
    '<!-- IF !$bb_cfg[\'emailer\'][\'enabled\'] or $bb_cfg[\'email_change_disabled\'] -->content<!-- ENDIF -->',
    '<!-- IF $bb_cfg[\'use_ajax_posts\'] && !IS_GUEST -->content<!-- ENDIF -->',
    '<!-- IF $bb_cfg[\'use_ajax_posts\'] && (AUTH_DELETE || AUTH_REPLY || AUTH_EDIT) -->content<!-- ENDIF -->',
    '<!-- IF ADMIN_LOGIN || AUTOLOGIN_DISABLED -->content<!-- ENDIF -->',
    '<!-- IF HAVE_NEW_PM || HAVE_UNREAD_PM -->content<!-- ENDIF -->',
    '<!-- IF IS_ADMIN || $bb_cfg[\'show_email_visibility_settings\'] -->content<!-- ENDIF -->',

    // Comparison operators
    '<!-- IF DOWN_TOTAL_BYTES gt MIN_DL_BYTES -->content<!-- ENDIF -->',
    '<!-- IF t.STATUS == MOVED -->content<!-- ENDIF -->',
    '<!-- IF t.STATUS != MOVED -->content<!-- ENDIF -->',
    '<!-- IF torrent.TOR_SILVER_GOLD == 1 -->content<!-- ENDIF -->',

    // Block variables
    '<!-- IF c.f.POSTS -->content<!-- ENDIF -->',
    '<!-- IF c.f.FORUM_DESC -->content<!-- ENDIF -->',
    '<!-- IF c.f.MODERATORS && SHOW_MOD_INDEX -->content<!-- ENDIF -->',
    '<!-- IF f.last.FORUM_LAST_POST -->content<!-- ENDIF -->',

    // Twig 'is' test
    '<!-- IF t.p.ROW_NUM is even -->content<!-- ENDIF -->',

    // ELSEIF patterns
    '<!-- IF t.DL -->a<!-- ELSEIF t.ATTACH -->b<!-- ENDIF -->',
    '<!-- IF torrent.TOR_SILVER_GOLD == 1 -->gold<!-- ELSEIF torrent.TOR_SILVER_GOLD == 2 -->silver<!-- ENDIF -->',

    // IF with ELSE
    '<!-- IF IS_GUEST -->guest<!-- ELSE -->user<!-- ENDIF -->',
    '<!-- IF not tor.TOR_FROZEN -->active<!-- ELSE -->frozen<!-- ENDIF -->',

    // Complex nested
    '<!-- IF postrow.IS_FIRST_POST || !$bb_cfg[\'show_post_bbcode_button\'][\'only_for_first_post\'] -->content<!-- ENDIF -->',
    '<!-- IF TOPIC_HAS_POLL or CAN_MANAGE_POLL -->content<!-- ENDIF -->',

    // Negation patterns
    '<!-- IF not IS_GUEST or $bb_cfg[\'tor_thanks_list_guests\'] -->content<!-- ENDIF -->',
    '<!-- IF not POLL_ALREADY_VOTED && SHOW_VOTE_BTN -->content<!-- ENDIF -->',

    // Complex ELSEIF chains
    '<!-- IF SHOW_VOTE_BTN -->vote<!-- ELSEIF not SHOW_VOTE_BTN -->novote<!-- ENDIF -->',

    // Block with subblock patterns
    '<!-- IF t.tor.MAGNET -->content<!-- ENDIF -->',
    '<!-- IF torrent.tor_server.TORR_SERVER_M3U_LINK -->content<!-- ENDIF -->',
];

echo "=== Testing Real Template Patterns ===\n\n";

$passed = 0;
$failed = 0;
$errors = [];

foreach ($patterns as $input) {
    try {
        $result = $converter->convertLegacySyntax($input);

        // Check for obvious errors
        $hasError = false;
        $errorMsg = '';

        // Check if result still contains legacy tags
        if (preg_match('/<!-- (IF|ELSE|ENDIF|ELSEIF|BEGIN|END)/', $result)) {
            $hasError = true;
            $errorMsg = 'Contains unconverted legacy tags';
        }

        // Check if result contains broken patterns like V.ELSE or V.IF
        if (preg_match('/V\.(IF|ELSE|ENDIF|ELSEIF)/', $result)) {
            $hasError = true;
            $errorMsg = 'Contains broken V.IF/V.ELSE patterns';
        }

        // Check for balanced braces
        $openCount = substr_count($result, '{%');
        $closeCount = substr_count($result, '%}');
        if ($openCount !== $closeCount) {
            $hasError = true;
            $errorMsg = "Unbalanced braces: $openCount open, $closeCount close";
        }

        if ($hasError) {
            echo "✗ FAILED: $errorMsg\n";
            echo "  Input:  $input\n";
            echo "  Output: $result\n\n";
            $failed++;
            $errors[] = ['input' => $input, 'output' => $result, 'error' => $errorMsg];
        } else {
            echo "✓ ";
            // Show shortened input
            $shortInput = strlen($input) > 60 ? substr($input, 0, 60) . '...' : $input;
            echo "$shortInput\n";
            $passed++;
        }
    } catch (Exception $e) {
        echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
        echo "  Input: $input\n\n";
        $failed++;
        $errors[] = ['input' => $input, 'output' => '', 'error' => $e->getMessage()];
    }
}

echo "\n=== Results ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total:  " . ($passed + $failed) . "\n";

if ($failed > 0) {
    echo "\n=== Failed Patterns ===\n";
    foreach ($errors as $err) {
        echo "\nInput:  {$err['input']}\n";
        echo "Output: {$err['output']}\n";
        echo "Error:  {$err['error']}\n";
    }
    exit(1);
}

exit(0);
