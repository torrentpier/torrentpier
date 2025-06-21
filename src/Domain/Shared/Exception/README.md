# Shared Domain Exceptions

Base exception classes used across all bounded contexts:

- `DomainException`: Base domain exception
- `InvalidArgumentException`: Invalid input validation
- `EntityNotFoundException`: Generic entity not found
- `BusinessRuleViolationException`: Business rule violations

These provide common exception handling patterns without coupling contexts.