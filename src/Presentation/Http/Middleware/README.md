# HTTP Middleware

Request/response pipeline middleware:
- `AuthenticationMiddleware`: User authentication
- `AuthorizationMiddleware`: Permission checks
- `CsrfProtectionMiddleware`: CSRF token validation
- `RateLimitMiddleware`: Request throttling
- `LocalizationMiddleware`: Language detection
- `CorsMiddleware`: Cross-origin resource sharing

Middleware follows PSR-15 standard.