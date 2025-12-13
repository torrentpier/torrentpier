<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Base command class for TorrentPier CLI commands
 *
 * Provides common functionality and helper methods for all commands
 */
abstract class Command extends SymfonyCommand
{
    protected SymfonyStyle $io;

    protected InputInterface $input;

    protected OutputInterface $output;

    /**
     * Initialize the command
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Display a success message
     */
    protected function success(string|array $message): void
    {
        $this->io->success($message);
    }

    /**
     * Display an error message
     */
    protected function error(string|array $message): void
    {
        $this->io->error($message);
    }

    /**
     * Display a warning message
     */
    protected function warning(string|array $message): void
    {
        $this->io->warning($message);
    }

    /**
     * Display an info message
     */
    protected function info(string $message): void
    {
        $this->io->info($message);
    }

    /**
     * Display a comment/note
     */
    protected function comment(string $message): void
    {
        $this->io->comment($message);
    }

    /**
     * Display a line of text
     */
    protected function line(string $message = ''): void
    {
        $this->output->writeln($message);
    }

    /**
     * Display a table
     */
    protected function table(array $headers, array $rows): void
    {
        $this->io->table($headers, $rows);
    }

    /**
     * Ask a question
     */
    protected function ask(string $question, ?string $default = null): ?string
    {
        return $this->io->ask($question, $default);
    }

    /**
     * Ask for confirmation
     */
    protected function confirm(string $question, bool $default = false): bool
    {
        return $this->io->confirm($question, $default);
    }

    /**
     * Ask for a choice
     */
    protected function choice(string $question, array $choices, mixed $default = null): mixed
    {
        return $this->io->choice($question, $choices, $default);
    }

    /**
     * Create a progress bar
     */
    protected function createProgressBar(int $max = 0): \Symfony\Component\Console\Helper\ProgressBar
    {
        return $this->io->createProgressBar($max);
    }

    /**
     * Display section title
     */
    protected function section(string $title): void
    {
        $this->io->section($title);
    }

    /**
     * Display title
     */
    protected function title(string $title): void
    {
        $this->io->title($title);
    }

    /**
     * Display a listing
     */
    protected function listing(array $items): void
    {
        $this->io->listing($items);
    }

    /**
     * Display a definition list
     */
    protected function definitionList(mixed ...$list): void
    {
        $this->io->definitionList(...$list);
    }

    /**
     * Check if verbose output is enabled
     */
    protected function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    /**
     * Check if very verbose output is enabled
     */
    protected function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * Check if the debug output is enabled
     */
    protected function isDebug(): bool
    {
        return $this->output->isDebug();
    }
}
