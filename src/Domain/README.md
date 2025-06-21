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