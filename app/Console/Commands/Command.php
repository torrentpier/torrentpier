<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Illuminate\Container\Container;

/**
 * Base Command Class
 *
 * Laravel-style base command class for TorrentPier console commands
 */
abstract class Command extends SymfonyCommand
{
    /**
     * The command signature (Laravel-style)
     * Example: 'cache:clear {--force : Force clearing without confirmation}'
     */
    protected string $signature = '';

    /**
     * The command description
     */
    protected string $description = '';

    /**
     * Application container
     */
    protected Container $app;

    /**
     * Console input interface
     */
    protected InputInterface $input;

    /**
     * Console output interface
     */
    protected OutputInterface $output;

    /**
     * Symfony style interface
     */
    protected SymfonyStyle $io;

    /**
     * Create a new command instance
     */
    public function __construct(?string $name = null)
    {
        // Parse signature if provided
        if ($this->signature) {
            $name = $this->parseSignature();
        }

        parent::__construct($name);

        // Set description
        if ($this->description) {
            $this->setDescription($this->description);
        }

        // Get container instance
        $this->app = Container::getInstance();
    }

    /**
     * Execute the command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        try {
            $result = $this->handle();
            return is_int($result) ? $result : self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Command failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Handle the command (implement in subclasses)
     */
    abstract public function handle(): int;

    /**
     * Parse Laravel-style signature
     */
    protected function parseSignature(): string
    {
        // Simple signature parsing - just extract command name for now
        // Full Laravel signature parsing would be more complex
        $parts = explode(' ', trim($this->signature));
        return $parts[0];
    }

    /**
     * Get an argument value
     */
    protected function argument(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get an option value
     */
    protected function option(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Display an info message
     */
    protected function info(string $message): void
    {
        $this->io->info($message);
    }

    /**
     * Display an error message
     */
    protected function error(string $message): void
    {
        $this->io->error($message);
    }

    /**
     * Display a warning message
     */
    protected function warn(string $message): void
    {
        $this->io->warning($message);
    }

    /**
     * Display a success message
     */
    protected function success(string $message): void
    {
        $this->io->success($message);
    }

    /**
     * Display a line of text
     */
    protected function line(string $message): void
    {
        $this->output->writeln($message);
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
     * Ask the user to select from a list of options
     */
    protected function choice(string $question, array $choices, ?string $default = null): string
    {
        return $this->io->choice($question, $choices, $default);
    }

    /**
     * Get application configuration
     */
    protected function config(?string $key = null, mixed $default = null): mixed
    {
        $config = $this->app->make('config');

        if ($key === null) {
            return $config;
        }

        return $config->get($key, $default);
    }
}
