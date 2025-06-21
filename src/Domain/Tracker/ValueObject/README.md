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