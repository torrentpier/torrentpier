# Domain Events

Base classes and interfaces for domain event system:

- `DomainEvent`: Base event interface
- `EventRecording`: Trait for aggregate event recording
- `AggregateHistory`: Event sourcing support

Example events:

- `UserRegisteredEvent`
- `TorrentUploadedEvent`
- `ThreadCreatedEvent`

Events enable loose coupling between bounded contexts.