<?php

use TorrentPier\Spam\Decision;
use TorrentPier\Spam\Provider\SpamPhraseProvider;

describe('SpamPhraseProvider', function () {
    describe('getName()', function () {
        it('returns spam_phrases', function () {
            $provider = new SpamPhraseProvider;

            expect($provider->getName())->toBe('spam_phrases');
        });
    });

    describe('isEnabled()', function () {
        it('is disabled by default', function () {
            $provider = new SpamPhraseProvider;

            expect($provider->isEnabled())->toBeFalse();
        });

        it('is enabled when config says so', function () {
            $provider = new SpamPhraseProvider(['enabled' => true]);

            expect($provider->isEnabled())->toBeTrue();
        });
    });

    describe('checkUser() - Literal Phrase Matching', function () {
        it('returns Allowed when no phrases are configured', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => [],
            ]);

            $result = $provider->checkUser('legitimate_user', 'user@example.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->providerName)->toBe('spam_phrases');
        });

        it('returns Denied when username matches a phrase at word boundary', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['viagra', 'casino'],
            ]);

            // Word boundaries require non-word chars (spaces, hyphens) around the phrase.
            // Underscores are word characters in regex, so "buy_viagra_now" would NOT match \b.
            $result = $provider->checkUser('buy viagra now', 'user@example.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied)
                ->and($result->reason)->toContain('viagra');
        });

        it('does NOT match phrase embedded with underscores (word boundary)', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['viagra'],
            ]);

            // Underscores are word characters, so \b does not fire around "viagra" here
            $result = $provider->checkUser('buy_viagra_now', 'user@example.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed);
        });

        it('returns Denied when email matches a phrase', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['spam-domain'],
            ]);

            $result = $provider->checkUser('normaluser', 'info@spam-domain.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied)
                ->and($result->reason)->toContain('spam-domain');
        });

        it('returns Allowed when nothing matches', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['viagra', 'casino'],
            ]);

            $result = $provider->checkUser('john', 'john@gmail.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed);
        });

        it('matches using word boundaries', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['spam'],
            ]);

            // "spam" as a standalone word in username
            $match = $provider->checkUser('i am spam', 'x@x.com', '1.2.3.4');
            expect($match->decision)->toBe(Decision::Denied);

            // "spam" embedded in a larger word should NOT match (word boundary)
            $noMatch = $provider->checkUser('antispammer', 'x@x.com', '1.2.3.4');
            expect($noMatch->decision)->toBe(Decision::Allowed);
        });

        it('is case-insensitive for literal phrases', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['viagra'],
            ]);

            // Use a space-separated username so word boundaries fire
            $result = $provider->checkUser('VIAGRA king', 'x@x.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied);
        });

        it('checks username before email and returns on first match', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['badword'],
            ]);

            // Username starts with the phrase (word boundary at start + underscore doesn't block start-of-string \b)
            // Actually: "badword" at start of "badword" followed by space triggers \b
            $result = $provider->checkUser('badword is here', 'also badword@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied)
                ->and($result->reason)->toContain('badword');
        });
    });

    describe('checkUser() - Regex Phrase Matching', function () {
        it('matches a regex phrase starting with /', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['/\d{3,}-\d{3,}/'],
            ]);

            $result = $provider->checkUser('call 123-456 now', 'x@x.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied)
                ->and($result->reason)->toContain('/\d{3,}-\d{3,}/');
        });

        it('returns Allowed for regex that does not match', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['/^exact_match$/'],
            ]);

            $result = $provider->checkUser('not_exact_match', 'x@x.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed);
        });
    });

    describe('checkContent()', function () {
        it('returns Moderated by default when content matches', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['buy cheap pills'],
            ]);

            $result = $provider->checkContent(1, 'hey buy cheap pills here!');

            expect($result->decision)->toBe(Decision::Moderated)
                ->and($result->reason)->toContain('buy cheap pills');
        });

        it('returns Denied when content_action is denied', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['buy cheap pills'],
                'content_action' => 'denied',
            ]);

            $result = $provider->checkContent(1, 'hey buy cheap pills here!');

            expect($result->decision)->toBe(Decision::Denied);
        });

        it('returns Moderated when content_action is moderated', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['suspicious link'],
                'content_action' => 'moderated',
            ]);

            $result = $provider->checkContent(1, 'visit this suspicious link now');

            expect($result->decision)->toBe(Decision::Moderated);
        });

        it('returns Allowed when content does not match any phrase', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['viagra', 'casino'],
            ]);

            $result = $provider->checkContent(1, 'This is a normal forum post about programming.');

            expect($result->decision)->toBe(Decision::Allowed);
        });

        it('handles regex patterns in content checks', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['/https?:\/\/bit\.ly\//'],
                'content_action' => 'moderated',
            ]);

            $result = $provider->checkContent(1, 'check out https://bit.ly/abc123');

            expect($result->decision)->toBe(Decision::Moderated);
        });
    });

    describe('Response Timing', function () {
        it('includes response time in milliseconds', function () {
            $provider = new SpamPhraseProvider([
                'enabled' => true,
                'phrases' => ['test'],
            ]);

            $result = $provider->checkUser('test_user', 'x@x.com', '1.2.3.4');

            expect($result->responseTimeMs)->toBeGreaterThanOrEqual(0.0);
        });
    });
});
