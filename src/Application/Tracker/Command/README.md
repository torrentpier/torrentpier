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