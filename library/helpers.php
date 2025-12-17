<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/**
 * Container access helpers
 *
 * Functions for accessing services from the container:
 * app(), config(), DB(), CACHE(), request(), user(), template(), lang(), etc.
 */

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Get the Application container instance
 *
 * Returns the Application instance created during bootstrap.
 * All requests must go through Front Controller (public/index.php or bull CLI).
 *
 * @param string|null $abstract Service to resolve from the container
 * @param array $parameters Parameters for the service resolution
 * @throws RuntimeException If the application is not bootstrapped
 * @throws BindingResolutionException
 * @return mixed Application instance or resolved service
 */
function app(?string $abstract = null, array $parameters = []): mixed
{
    $app = Container::getInstance();

    if ($app === null) {
        throw new RuntimeException('Application not bootstrapped. All requests must go through Front Controller.');
    }

    if ($abstract === null) {
        return $app;
    }

    return $app->make($abstract, $parameters);
}

/**
 * Get the Config instance
 * @throws BindingResolutionException
 */
function config(): TorrentPier\Config
{
    return app(TorrentPier\Config::class);
}

/**
 * Get the Filesystem instance
 *
 * Provides access to file operations with TorrentPier-specific features
 * like log rotation and proper umask handling for directories.
 *
 * Usage:
 *   // Reading files
 *   files()->get('/path/to/file')              // Get file contents
 *   files()->json('/path/to/file.json')        // Get JSON decoded
 *   files()->lines('/path/to/file')            // Get LazyCollection of lines
 *   files()->exists('/path/to/file')           // Check existence
 *   files()->isFile('/path/to/file')           // Check if file (not dir)
 *   files()->isDirectory('/path/to/dir')       // Check if directory
 *
 *   // Writing files
 *   files()->put($path, $content)              // Write/replace content
 *   files()->write($path, $content, lock: true) // Write with locking
 *   files()->append($path, $content)           // Append to file
 *   files()->appendWithRotation($path, $content, maxSize: 1048576) // Log rotation
 *
 *   // File operations
 *   files()->delete($path)                     // Delete file(s)
 *   files()->move($from, $to)                  // Move/rename file
 *   files()->copy($from, $to)                  // Copy file
 *
 *   // Directory operations
 *   files()->ensureDirectoryExists($path)      // Create dir with umask handling
 *   files()->makeDirectory($path, 0755, true)  // Create directory
 *   files()->deleteDirectory($path)            // Delete directory recursively
 *   files()->cleanDirectory($path)             // Empty directory
 *
 *   // File info
 *   files()->size($path)                       // Get file size
 *   files()->lastModified($path)               // Get modification time
 *   files()->mimeType($path)                   // Get MIME type
 *   files()->hash($path)                       // Get file hash
 *
 *   // Directory listing
 *   files()->files($dir)                       // Get files in directory
 *   files()->allFiles($dir)                    // Get all files recursively
 *   files()->directories($dir)                 // Get subdirectories
 *   files()->glob($pattern)                    // Find by pattern
 *
 * @throws BindingResolutionException
 */
function files(): TorrentPier\Filesystem\Filesystem
{
    return app(TorrentPier\Filesystem\Filesystem::class);
}

/**
 * Get the HTTP Client instance
 * @throws BindingResolutionException
 */
function httpClient(): TorrentPier\Http\HttpClient
{
    return app(TorrentPier\Http\HttpClient::class);
}

/**
 * Get the HTTP Request instance
 *
 * Provides typed access to request parameters ($_GET, $_POST, $_COOKIE, $_SERVER, $_FILES)
 *
 * Usage:
 *   // Typed getters (POST priority over GET)
 *   request()->get('key')                   // mixed, from POST or GET
 *   request()->has('key')                   // bool, check if parameter exists
 *   request()->all()                        // array, all merged parameters
 *   request()->getInt('topic_id')           // int
 *   request()->getString('mode')            // string
 *   request()->getBool('flag')              // bool
 *   request()->getFloat('ratio')            // float
 *   request()->getArray('ids')              // array
 *
 *   // Specific source via properties (Symfony InputBag)
 *   request()->query->get('f')              // GET parameter
 *   request()->post->get('message')         // POST parameter
 *   request()->cookies->get('session_id')   // Cookie
 *   request()->server->get('REQUEST_URI')   // Server var
 *   request()->headers->get('User-Agent')   // Header
 *   request()->files->get('upload')         // Uploaded file
 *
 *   // Request metadata
 *   request()->getMethod()                  // HTTP method (GET, POST, etc.)
 *   request()->isPost()                     // Check if POST
 *   request()->isGet()                      // Check if GET
 *   request()->isAjax()                     // Check if AJAX/XHR
 *   request()->isSecure()                   // Check if HTTPS
 *   request()->getClientIp()                // Client IP address
 *   request()->getRequestUri()              // URI with query string
 *   request()->getPathInfo()                // Path without query string
 *   request()->getQueryString()             // Query string only
 *   request()->getHost()                    // Host name
 *   request()->getScheme()                  // http or https
 *   request()->getUserAgent()               // User-Agent header
 *   request()->getReferer()                 // Referer header
 *   request()->getContentType()             // Content-Type
 *   request()->getContent()                 // Raw request body
 *   request()->getSymfonyRequest()          // Underlying Symfony Request
 * @throws BindingResolutionException
 */
function request(): TorrentPier\Http\Request
{
    return app(TorrentPier\Http\Request::class);
}

/**
 * Get the Censor instance
 * @throws BindingResolutionException
 */
function censor(): TorrentPier\Censor
{
    return app(TorrentPier\Censor::class);
}

/**
 * Whoops error handler singleton
 * @throws BindingResolutionException
 */
function whoops(): TorrentPier\Whoops\WhoopsManager
{
    return app(TorrentPier\Whoops\WhoopsManager::class);
}

/**
 * Tracy debug bar singleton
 * @throws BindingResolutionException
 */
function tracy(): TorrentPier\Tracy\TracyBarManager
{
    return app(TorrentPier\Tracy\TracyBarManager::class);
}

/**
 * Get the Language instance
 * @throws BindingResolutionException
 */
function lang(): TorrentPier\Language
{
    return app(TorrentPier\Language::class);
}

/**
 * Get a language string (shorthand for lang()->get())
 *
 * @param string $key Language key supports dot notation (e.g., 'DATETIME.TODAY')
 * @param mixed $default Default value if the key doesn't exist
 * @throws BindingResolutionException
 * @return mixed Language string or default value
 */
function __(string $key, mixed $default = null): mixed
{
    return app(TorrentPier\Language::class)->get($key, $default);
}

/**
 * Echo a language string (shorthand for echo __())
 *
 * @param string $key Language key supports dot notation
 * @param mixed $default Default value if the key doesn't exist
 * @throws BindingResolutionException
 */
function _e(string $key, mixed $default = null): void
{
    echo app(TorrentPier\Language::class)->get($key, $default);
}

/**
 * Get the Template instance
 *
 * When $root is provided, creates a new Template and registers it in the container.
 * When $root is null, returns the previously registered instance.
 *
 * @param string|null $root Template root directory (pass on the first call to initialize)
 * @throws RuntimeException If called without $root before initialization
 * @throws BindingResolutionException
 */
function template(?string $root = null): TorrentPier\Template\Template
{
    if ($root !== null) {
        $template = new TorrentPier\Template\Template($root);
        app()->instance(TorrentPier\Template\Template::class, $template);

        return $template;
    }

    return app(TorrentPier\Template\Template::class);
}

/**
 * Get theme images array
 *
 * @param string|null $key Specific image key, or null for all images
 * @throws BindingResolutionException
 * @return mixed Image path, all images array, or empty string if key not found
 */
function theme_images(?string $key = null): mixed
{
    $twig = template()->getTwig();
    if (!$twig) {
        return $key === null ? [] : '';
    }

    $themeVars = $twig->getGlobals();
    $images = $themeVars['images'] ?? [];

    if ($key === null) {
        return $images;
    }

    return $images[$key] ?? '';
}

/**
 * Get the Database instance (Nette Database)
 * @throws BindingResolutionException
 */
function DB(): TorrentPier\Database\Database
{
    return app(TorrentPier\Database\Database::class);
}

/**
 * Get Eloquent Capsule Manager instance
 *
 * Use this to access Eloquent ORM directly:
 * - eloquent()->connection() - get PDO connection
 * - eloquent()->table('bb_users') - query builder
 *
 * For models, use them directly: User::find(1), Topic::where(...)->get()
 *
 * @throws BindingResolutionException
 */
function eloquent(): Illuminate\Database\Capsule\Manager
{
    return app(Illuminate\Database\Capsule\Manager::class);
}

/**
 * Get cache manager instance (replaces a legacy cache system)
 * @throws BindingResolutionException
 */
function CACHE(string $cache_name): TorrentPier\Cache\CacheManager
{
    return app(TorrentPier\Cache\UnifiedCacheSystem::class)->get_cache_obj($cache_name);
}

/**
 * Get datastore manager instance (replaces legacy datastore system)
 * @throws BindingResolutionException
 */
function datastore(): TorrentPier\Cache\DatastoreManager
{
    return app(TorrentPier\Cache\DatastoreManager::class);
}

/**
 * User singleton helper
 * @throws BindingResolutionException
 */
function user(): TorrentPier\Legacy\Common\User
{
    return app(TorrentPier\Legacy\Common\User::class);
}

/**
 * Userdata helper - returns user data array
 *
 * @param string|null $key Optional key to get a specific value
 * @throws BindingResolutionException
 */
function userdata(?string $key = null): mixed
{
    $data = user()->data;

    return $key === null ? $data : ($data[$key] ?? null);
}

/**
 * LogAction singleton helper
 * @throws BindingResolutionException
 */
function log_action(): TorrentPier\Legacy\LogAction
{
    return app(TorrentPier\Legacy\LogAction::class);
}

/**
 * Html helper singleton
 * @throws BindingResolutionException
 */
function html(): TorrentPier\Legacy\Common\Html
{
    return app(TorrentPier\Legacy\Common\Html::class);
}

/**
 * BBCode parser singleton
 * @throws BindingResolutionException
 */
function bbcode(): TorrentPier\Legacy\BBCode
{
    return app(TorrentPier\Legacy\BBCode::class);
}

/**
 * Manticore search singleton
 * @throws BindingResolutionException
 */
function manticore(): ?TorrentPier\ManticoreSearch
{
    return app(TorrentPier\ManticoreSearch::class);
}

/**
 * Read tracker singleton - tracks read status of topics and forums
 * @throws BindingResolutionException
 */
function read_tracker(): TorrentPier\ReadTracker
{
    return app(TorrentPier\ReadTracker::class);
}

/**
 * Get topic tracking data
 *
 * @throws BindingResolutionException
 * @return array Reference to a tracking array
 */
function &tracking_topics(): array
{
    return read_tracker()->getTopics();
}

/**
 * Get forum tracking data
 *
 * @throws BindingResolutionException
 * @return array Reference to a tracking array
 */
function &tracking_forums(): array
{
    return read_tracker()->getForums();
}

/**
 * Get forum tree data (categories and forums hierarchy)
 *
 * @param bool $refresh Refresh cached data before returning
 * @throws BindingResolutionException
 * @return array Forum tree data
 */
function forum_tree(bool $refresh = false): array
{
    $instance = app(TorrentPier\Forum\ForumTree::class);
    if ($refresh) {
        $instance->refresh();
    }

    return $instance->get();
}
