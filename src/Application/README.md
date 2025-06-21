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