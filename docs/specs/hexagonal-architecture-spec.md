# Hexagonal Architecture Directory Structure Specification

## Overview

This document specifies the new hexagonal architecture directory structure for TorrentPier 3.0. The structure follows Domain-Driven Design (DDD) principles and implements a clean separation of concerns through hexagonal architecture (ports and adapters pattern).

## Directory Structure

```
/src/
├── Domain/                # Core business logic - no framework dependencies
│   ├── Forum/             # Forum bounded context
│   │   ├── Model/         # Aggregates and entities
│   │   ├── ValueObject/   # Value objects (PostId, ThreadTitle, etc.)
│   │   ├── Repository/    # Repository interfaces
│   │   └── Exception/     # Domain-specific exceptions
│   ├── Tracker/           # BitTorrent tracker bounded context
│   │   ├── Model/         # Torrent, Peer aggregates
│   │   ├── ValueObject/   # InfoHash, PeerId, etc.
│   │   ├── Repository/    # Repository interfaces
│   │   └── Exception/     # Tracker-specific exceptions
│   ├── User/              # User management bounded context
│   │   ├── Model/         # User aggregate
│   │   ├── ValueObject/   # UserId, Email, Username
│   │   ├── Repository/    # User repository interface
│   │   └── Exception/     # Authentication/authorization exceptions
│   └── Shared/            # Shared kernel - minimal shared concepts
│       ├── Model/         # Base classes (AggregateRoot, Entity)
│       ├── ValueObject/   # Common value objects (Id, DateTime)
│       └── Event/         # Domain events base classes
│
├── Application/           # Application services - orchestration layer
│   ├── Forum/
│   │   ├── Command/       # Commands (CreatePost, LockThread)
│   │   ├── Query/         # Queries (GetThreadList, SearchPosts)
│   │   └── Handler/       # Command and query handlers
│   ├── Tracker/
│   │   ├── Command/       # Commands (RegisterTorrent, ProcessAnnounce)
│   │   ├── Query/         # Queries (GetPeerList, GetTorrentStats)
│   │   └── Handler/       # Command and query handlers
│   └── User/
│       ├── Command/       # Commands (RegisterUser, ChangePassword)
│       ├── Query/         # Queries (GetUserProfile, SearchUsers)
│       └── Handler/       # Command and query handlers
│
├── Infrastructure/        # External concerns and implementations
│   ├── Persistence/       # Data persistence layer
│   │   ├── Database/      # Database adapter and connection management
│   │   ├── Migration/     # Database migrations
│   │   └── Repository/    # Repository implementations
│   ├── Cache/             # Caching implementations
│   │   ├── Redis/         # Redis adapter
│   │   ├── Memcached/     # Memcached adapter
│   │   └── File/          # File-based cache adapter
│   ├── Email/             # Email service implementations
│   │   ├── Template/      # Email templates
│   │   └── Transport/     # SMTP, API transports
│   └── FileStorage/       # File storage abstractions
│       ├── Local/         # Local filesystem storage
│       └── S3/            # AWS S3 storage adapter
│
└── Presentation/          # User interface layer
    ├── Http/              # Web interface
    │   ├── Controllers/   # HTTP controllers
    │   │   ├── Admin/     # Admin panel controllers
    │   │   ├── Api/       # REST API controllers
    │   │   └── Web/       # Web UI controllers
    │   ├── Middleware/    # HTTP middleware (auth, CORS, etc.)
    │   ├── Requests/      # Request DTOs and validation
    │   └── Responses/     # Response transformers
    └── Cli/               # Command line interface
        └── Commands/      # Console commands

# Additional directories (outside /src/)
/config/                   # Application configuration
├── app.php                # Main application settings
├── database.php           # Database connections
├── cache.php              # Cache drivers configuration
├── tracker.php            # BitTorrent tracker settings
└── environments/          # Environment-specific overrides

/tests/                    # Test suites (Pest)
├── Unit/                  # Unit tests (mirrors src/ structure)
├── Feature/               # Feature/Integration tests
├── Pest.php               # Pest configuration
└── TestCase.php           # Base test case
```

## Directory README.md Templates

### Domain Layer READMEs

#### `/src/Domain/README.md`
```markdown
# Domain Layer

This directory contains the core business logic of TorrentPier. Code here should:
- Have no dependencies on frameworks or infrastructure
- Represent pure business rules and domain models
- Be testable in isolation
- Use only PHP language features and domain concepts

## Bounded Contexts
- **Forum**: Discussion forums, posts, threads
- **Tracker**: BitTorrent tracking, peers, torrents
- **User**: User management, authentication, profiles
- **Shared**: Minimal shared concepts between contexts
```

#### `/src/Domain/Tracker/Model/README.md`
```markdown
# Tracker Domain Models

Contains aggregate roots and entities for the BitTorrent tracker:
- `Torrent`: Aggregate root for torrent management
- `Peer`: Entity representing a BitTorrent peer
- `TorrentStatistics`: Value object for torrent stats

Example:
```php
class Torrent extends AggregateRoot
{
    public function announce(Peer $peer, AnnounceEvent $event): void
    {
        // Business logic for handling announces
    }
}
```

#### `/src/Domain/Tracker/ValueObject/README.md`
```markdown
# Tracker Value Objects

Immutable objects representing domain concepts:
- `InfoHash`: 20-byte torrent identifier
- `PeerId`: Peer client identifier
- `Port`: Network port (1-65535)
- `BytesTransferred`: Upload/download bytes

Example:
```php
final class InfoHash
{
    private string $hash;
    
    public function __construct(string $hash)
    {
        $this->guardAgainstInvalidHash($hash);
        $this->hash = $hash;
    }
}
```

### Application Layer READMEs

#### `/src/Application/README.md`
```markdown
# Application Layer

Contains application services that orchestrate domain objects to fulfill use cases.
- Commands: Write operations that change state
- Queries: Read operations for data retrieval
- Handlers: Process commands and queries

This layer should:
- Coordinate domain objects
- Handle transactions
- Dispatch domain events
- Not contain business logic
```

#### `/src/Application/Tracker/Command/README.md`
```markdown
# Tracker Commands

Commands representing write operations:
- `RegisterTorrentCommand`: Register new torrent
- `UpdateTorrentCommand`: Modify torrent details
- `DeleteTorrentCommand`: Remove torrent from tracker

Example:
```php
final class RegisterTorrentCommand
{
    public function __construct(
        public readonly string $infoHash,
        public readonly int $uploaderId,
        public readonly string $name,
        public readonly int $size
    ) {}
}
```

### Infrastructure Layer READMEs

#### `/src/Infrastructure/README.md`
```markdown
# Infrastructure Layer

Technical implementations and external service adapters:
- Database persistence
- Caching mechanisms
- Email services
- File storage
- Third-party integrations

Infrastructure depends on domain, not vice versa.
```

#### `/src/Infrastructure/Persistence/Repository/README.md`
```markdown
# Repository Implementations

Concrete implementations of domain repository interfaces:
- Uses database adapter for persistence
- Implements caching strategies
- Handles query optimization
- Supports multiple database backends

Example:
```php
class TorrentRepository implements TorrentRepositoryInterface
{
    public function __construct(
        private DatabaseAdapterInterface $db
    ) {}
    
    public function findByInfoHash(InfoHash $infoHash): ?Torrent
    {
        // Database adapter implementation
        $row = $this->db->select('torrents')
            ->where('info_hash', $infoHash->toString())
            ->first();
            
        return $row ? $this->hydrateFromRow($row) : null;
    }
}
```

### Presentation Layer READMEs

#### `/src/Presentation/README.md`
```markdown
# Presentation Layer

User interface implementations:
- HTTP controllers for web and API
- CLI commands for console operations
- Request/response handling
- Input validation
- Output formatting

This layer translates between external format and application format.
```

#### `/src/Presentation/Http/Controllers/Api/README.md`
```markdown
# API Controllers

RESTful API endpoints following OpenAPI specification:
- JSON request/response format
- Proper HTTP status codes
- HATEOAS where applicable
- Rate limiting aware

Example:
```php
class UserController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $command = new RegisterUserCommand(
            $request->getUsername(),
            $request->getEmail(),
            $request->getPassword()
        );
        
        $userId = $this->commandBus->handle($command);
        
        return new JsonResponse([
            'id' => $userId,
            'username' => $request->getUsername()
        ], Response::HTTP_CREATED);
    }
}
```

#### `/src/Presentation/Http/Controllers/Admin/README.md`
```markdown
# Admin Panel Controllers

Administrative interface controllers with enhanced security:
- Role-based access control (RBAC)
- Audit logging for all actions
- Additional authentication checks
- Administrative dashboards and reports

Example:
```php
class AdminUserController
{
    public function index(Request $request): Response
    {
        $query = new GetUsersQuery(
            page: $request->getPage(),
            filters: $request->getFilters()
        );
        
        $users = $this->queryBus->handle($query);
        
        return $this->render('admin/users/index', [
            'users' => $users,
            'filters' => $request->getFilters()
        ]);
    }
}
```

#### `/config/README.md`
```markdown
# Application Configuration

System configuration files using PHP arrays for type safety and IDE support:

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

- **environments/**: Environment-specific overrides
  - Development, staging, production settings
  - Local developer configurations

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
            'database' => env('DB_DATABASE', 'tp'),
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
   - Set up base classes in Domain/Shared
   - Configure dependency injection

2. **Phase 2: Domain Modeling**
   - Implement core aggregates
   - Create value objects
   - Define repository interfaces

3. **Phase 3: Application Services**
   - Create commands and queries
   - Implement handlers
   - Set up event dispatching

4. **Phase 4: Infrastructure**
   - Implement repositories
   - Configure database adapter
   - Set up caching

5. **Phase 5: Presentation**
   - Create controllers
   - Implement middleware
   - Build CLI commands

## Migration Strategy

- Existing code remains in current locations
- New features built in hexagonal architecture
- Gradual migration using strangler fig pattern
- Legacy adapters bridge old and new code
- Feature flags control rollout

## Key Principles

1. **Dependency Rule**: Dependencies point inward (Presentation → Application → Domain)
2. **Domain Isolation**: Business logic has no framework dependencies
3. **Interface Segregation**: Small, focused interfaces
4. **CQRS**: Separate read and write models
5. **Event-Driven**: Domain events for cross-context communication

## Testing Strategy

- **Domain**: Pure unit tests, no mocks needed
- **Application**: Unit tests with mocked repositories
- **Infrastructure**: Integration tests with real services
- **Presentation**: E2E tests for user journeys

## Notes for Developers

- Start reading code from the Domain layer
- Business rules live in aggregates, not services
- Use value objects for type safety
- Prefer composition over inheritance
- Keep bounded contexts loosely coupled
