# MVC Architecture Directory Structure Specification

## Overview

This document specifies the MVC (Model-View-Controller) architecture directory structure for TorrentPier 3.0. The structure follows a simple, Laravel-inspired approach that prioritizes developer familiarity and ease of maintenance over complex enterprise patterns.

## Directory Structure

```
# Laravel-style root structure
/app/                      # Application code (PSR-4: App\)
├── Console/               # Console commands
│   └── Commands/          # Artisan-style commands
├── Http/                  # HTTP layer
│   ├── Controllers/       # Controllers
│   │   ├── Admin/         # Admin panel controllers
│   │   ├── Api/           # API controllers
│   │   └── Web/           # Web controllers
│   ├── Middleware/        # HTTP middleware
│   └── Requests/          # Form request validation
├── Models/                # Data models (using Nette Database)
│   ├── Forum/             # Forum models (Post, Thread, Forum)
│   ├── Tracker/           # Tracker models (Torrent, Peer, Announce)
│   └── User/              # User models (User, Profile, Permission)
├── Services/              # Business logic services
│   ├── Forum/             # Forum services
│   ├── Tracker/           # Tracker services
│   └── User/              # User services
├── Repositories/          # Data access layer (optional)
├── Events/                # Event classes
├── Listeners/             # Event listeners
├── Jobs/                  # Background jobs
├── Mail/                  # Mailable classes
├── Notifications/         # Notification classes
└── Exceptions/            # Exception handling

/bootstrap/                # Application bootstrap
├── app.php                # Application bootstrap
├── console.php            # Console bootstrap
└── cache/                 # Bootstrap cache

/config/                   # Configuration files
├── app.php                # Application config
├── auth.php               # Authentication config
├── cache.php              # Cache configuration
├── database.php           # Database connections
├── filesystems.php        # File storage config
├── tracker.php            # BitTorrent tracker settings
└── (legacy configs...)    # Existing config files

/database/                 # Database files
├── migrations/            # Database migrations (moved from /migrations/)
├── factories/             # Model factories for testing
└── seeders/               # Database seeders

/public/                   # Public web root
├── index.php              # Front controller
├── css/                   # Public CSS
├── js/                    # Public JavaScript
├── images/                # Public images
└── fonts/                 # Web fonts

/resources/                # Resources
├── views/                 # View templates
│   ├── admin/             # Admin panel views
│   ├── forum/             # Forum views
│   ├── tracker/           # Tracker views
│   └── layouts/           # Layout templates
├── lang/                  # Language files
├── js/                    # JavaScript source
└── css/                   # CSS/SCSS source

/routes/                   # Route definitions
├── web.php                # Web routes
├── api.php                # API routes
├── admin.php              # Admin routes
└── console.php            # Console routes

/src/                      # Framework/Infrastructure code (PSR-4: TorrentPier\)
├── Database/              # Database abstraction
├── Cache/                 # Cache system
├── Infrastructure/        # DI container, HTTP routing, etc.
├── Legacy/                # Legacy code adapters
└── helpers.php            # Global helper functions

/storage/                  # Storage directory
├── app/                   # Application storage
│   ├── public/            # Publicly accessible files
│   └── private/           # Private files
├── framework/             # Framework storage
│   ├── cache/             # File cache
│   ├── sessions/          # Session files
│   └── views/             # Compiled view cache
└── logs/                  # Application logs

/tests/                    # Test suites
├── Feature/               # Feature tests
├── Unit/                  # Unit tests
└── TestCase.php           # Base test case

# Legacy directories (being migrated)
/library/                  # Legacy core code
/controllers/              # Legacy PHP controllers  
/admin/                    # Legacy admin interface
/styles/                   # Legacy templates/assets
/internal_data/            # Legacy cache/logs

# Root files
.env                       # Environment variables
.env.example               # Environment example
composer.json              # Dependencies (App\ and TorrentPier\ namespaces)
artisan                    # CLI interface
index.php                  # Legacy entry point (redirects to public/)
```

## Directory README.md Templates

### Application Layer READMEs

#### `/app/README.md`
```markdown
# Application Directory

This directory contains the core application code following MVC pattern:
- **Models**: Database models and business entities
- **Controllers**: Handle HTTP requests and responses
- **Services**: Business logic and application services
- **Console**: CLI commands for maintenance and operations

## Key Components
- **Http**: Web and API controllers, middleware, requests
- **Models**: Database models using Nette Database
- **Services**: Reusable business logic
- **Events**: Application events and listeners
```

#### `/app/Models/Tracker/README.md`
```markdown
# Tracker Models

Database models for BitTorrent tracker functionality:
- `Torrent`: Torrent information and metadata
- `Peer`: Active peers in swarms
- `Announce`: Announce history and statistics

Example:
```php
class Torrent extends Model
{
    protected string $table = 'bb_torrents';
    
    public function getPeers(): array
    {
        return $this->db->table('bb_peers')
            ->where('torrent_id', $this->id)
            ->fetchAll();
    }
    
    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }
}
```

#### `/app/Services/Tracker/README.md`
```markdown
# Tracker Services

Business logic for tracker operations:
- `AnnounceService`: Handle peer announces
- `ScrapeService`: Provide torrent statistics
- `TorrentService`: Torrent management operations

Example:
```php
class AnnounceService
{
    public function __construct(
        private TorrentRepository $torrents,
        private PeerRepository $peers
    ) {}
    
    public function handleAnnounce(string $infoHash, array $data): array
    {
        $torrent = $this->torrents->findByInfoHash($infoHash);
        $peers = $this->peers->getActivePeers($torrent->id);
        
        return ['peers' => $peers, 'interval' => 900];
    }
}
```

### Controllers READMEs

#### `/app/Http/Controllers/README.md`
```markdown
# Controllers

HTTP controllers following RESTful conventions:
- Accept HTTP requests
- Validate input
- Call services for business logic
- Return appropriate responses

Controllers should be thin - delegate business logic to services.
```

#### `/app/Http/Controllers/Web/TrackerController.php`
```markdown
# Tracker Web Controller

Handles web interface for tracker functionality:

Example:
```php
class TrackerController extends Controller
{
    public function __construct(
        private TorrentService $torrentService
    ) {}
    
    public function index(Request $request)
    {
        $torrents = $this->torrentService->paginate(
            $request->get('page', 1),
            $request->get('category')
        );
        
        return view('tracker.index', compact('torrents'));
    }
    
    public function store(StoreTorrentRequest $request)
    {
        $torrent = $this->torrentService->create(
            $request->validated(),
            $request->user()
        );
        
        return redirect()->route('torrents.show', $torrent);
    }
}
```

### Services Layer READMEs

#### `/app/Services/README.md`
```markdown
# Services

Reusable business logic organized by feature:
- Encapsulate complex operations
- Coordinate between models
- Handle external integrations
- Maintain single responsibility

Services are injected into controllers and commands.
```

#### `/app/Repositories/README.md`
```markdown
# Repositories (Optional)

Data access layer for complex queries:
- Abstracts database queries from models
- Implements caching strategies
- Handles query optimization

Example:
```php
class TorrentRepository
{
    public function __construct(
        private Database $db,
        private CacheManager $cache
    ) {}
    
    public function findByInfoHash(string $infoHash): ?Torrent
    {
        return $this->cache->remember("torrent:{$infoHash}", 3600, function() use ($infoHash) {
            $data = $this->db->table('bb_torrents')
                ->where('info_hash', $infoHash)
                ->fetch();
            
            return $data ? new Torrent($data) : null;
        });
    }
    
    public function getPopularTorrents(int $limit = 10): array
    {
        return $this->db->table('bb_torrents')
            ->select('torrents.*, COUNT(peers.id) as peer_count')
            ->leftJoin('bb_peers', 'peers.torrent_id = torrents.id')
            ->groupBy('torrents.id')
            ->orderBy('peer_count DESC')
            ->limit($limit)
            ->fetchAll();
    }
}
```

### Views READMEs

#### `/resources/views/README.md`
```markdown
# Views

Template files for rendering HTML:
- Layouts for consistent structure
- Partials for reusable components
- Feature-specific views
- Email templates

Using PHP templates with simple helper functions.
```

#### `/app/Http/Controllers/Api/README.md`
```markdown
# API Controllers

RESTful API endpoints:
- JSON request/response format
- Proper HTTP status codes
- API versioning support
- Rate limiting aware

Example:
```php
class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}
    
    public function register(Request $request): JsonResponse
    {
        $validatedData = $this->validate($request, [
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);
        
        $user = $this->userService->register($validatedData);
        
        return response()->json([
            'id' => $user->id,
            'username' => $user->username
        ], 201);
    }
}
```

#### `/app/Http/Controllers/Admin/README.md`
```markdown
# Admin Panel Controllers

Administrative interface controllers:
- Protected by admin middleware
- Activity logging
- Bulk operations support
- Dashboard and reports

Example:
```php
class AdminUserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {
        $this->middleware('admin');
    }
    
    public function index(Request $request)
    {
        $query = $request->get('search');
        
        $users = $this->userService->searchUsers($query)
            ->paginate(20);
        
        return view('admin.users.index', compact('users'));
    }
}
```

#### `/config/README.md`
```markdown
# Application Configuration

System configuration files using PHP arrays:

- **app.php**: Core application settings
  - Site name, URL, timezone
  - Debug mode, environment
  - Feature flags and toggles

- **database.php**: Database connection settings
  - Multiple connection definitions
  - Read/write splitting configuration
  - Connection pooling settings

- **cache.php**: Cache driver configurations
  - Redis, Memcached, file-based settings
  - TTL defaults per cache type
  - Cache key prefixes

- **tracker.php**: BitTorrent tracker settings
  - Announce intervals
  - Peer limits
  - Ratio requirements

Example database configuration:
```php
<?php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'torrentpier'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
];
```

## Implementation Order

1. **Phase 1: Foundation**
   - Create directory structure
   - Set up routing files
   - Configure service providers
   - Set up middleware

2. **Phase 2: Models & Database**
   - Create database models
   - Define relationships
   - Set up migrations
   - Create seeders

3. **Phase 3: Services & Business Logic**
   - Implement service classes
   - Create repositories (if needed)
   - Set up events and listeners

4. **Phase 4: Controllers & Routes**
   - Create web controllers
   - Build API endpoints
   - Set up admin controllers
   - Define routes

5. **Phase 5: Views & Frontend**
   - Create template layouts
   - Build view components
   - Set up assets pipeline

## Migration Strategy

- Move existing controllers to /app/Http/Controllers/Legacy
- Gradually rewrite to new MVC structure
- Use service classes to encapsulate business logic
- Maintain backward compatibility through routing
- Progressive enhancement approach

## Key Principles

1. **Simplicity**: Straightforward MVC pattern
2. **Convention over Configuration**: Consistent naming and structure
3. **Fat Models, Skinny Controllers**: Business logic in models/services
4. **Service Layer**: Complex operations in service classes
5. **Repository Pattern**: Optional for complex queries

## Testing Strategy

- **Unit Tests**: Models and services
- **Feature Tests**: HTTP endpoints and user flows
- **Integration Tests**: Database and external services
- **Browser Tests**: Critical user journeys

## Notes for Developers

- Keep controllers thin, move logic to services
- Use dependency injection for testability
- Use Nette Database for data access
- Write readable code over clever code
- Focus on maintainability
