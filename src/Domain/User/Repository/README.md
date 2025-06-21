# User Repository Interface

Repository interface for user aggregate:
- `UserRepositoryInterface`: User persistence and retrieval
  - `findById(UserId $id): ?User`
  - `findByUsername(Username $username): ?User`
  - `findByEmail(Email $email): ?User`
  - `save(User $user): void`
  - `delete(User $user): void`