<?php

declare(strict_types=1);

namespace TorrentPier\ModSystem;

/**
 * Exception class for mod system errors
 *
 * @package TorrentPier\ModSystem
 * @since 3.0.0
 */
class ModException extends \Exception
{
    // Error codes
    public const MANIFEST_NOT_FOUND = 1001;
    public const MANIFEST_INVALID_JSON = 1002;
    public const MANIFEST_MISSING_FIELD = 1003;
    public const MANIFEST_INVALID_SCHEMA = 1004;

    public const COMPATIBILITY_TP_VERSION = 2001;
    public const COMPATIBILITY_PHP_VERSION = 2002;
    public const COMPATIBILITY_MISSING_DEPENDENCY = 2003;

    public const MOD_NOT_FOUND = 3001;
    public const MOD_ALREADY_INSTALLED = 3002;
    public const MOD_NOT_INSTALLED = 3003;
    public const MOD_ALREADY_ACTIVE = 3004;
    public const MOD_NOT_ACTIVE = 3005;
    public const MOD_DIRECTORY_NOT_WRITABLE = 3006;

    public const ACTIVATION_FAILED = 4001;
    public const DEACTIVATION_FAILED = 4002;
    public const INSTALLATION_FAILED = 4003;
    public const UNINSTALLATION_FAILED = 4004;

    public const DATABASE_ERROR = 5001;
    public const FILE_OPERATION_ERROR = 5002;
    public const BOOT_ERROR = 5003;

    /**
     * Additional context data for the exception
     *
     * @var array
     */
    protected array $context = [];

    /**
     * Constructor
     *
     * @param string $message Error message
     * @param int $code Error code (use class constants)
     * @param array $context Additional context data
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string      $message = "",
        int         $code = 0,
        array       $context = [],
        ?\Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get context data
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Add context data
     *
     * @param string $key Context key
     * @param mixed $value Context value
     * @return self
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Get full error details as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->context,
            'trace' => $this->getTraceAsString(),
        ];
    }

    /**
     * Get error details as JSON
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
