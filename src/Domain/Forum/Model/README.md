# Forum Domain Models

Contains aggregate roots and entities for the forum system:
- `Forum`: Forum category aggregate
- `Thread`: Discussion thread aggregate root
- `Post`: Individual post entity
- `Attachment`: File attachment entity

Business rules enforced at this level:
- Post editing time limits
- Thread locking rules
- Forum access permissions
- Post moderation workflow