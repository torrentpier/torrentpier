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