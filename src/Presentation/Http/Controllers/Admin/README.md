# Admin Panel Controllers

Administrative interface controllers with enhanced security:
- Role-based access control (RBAC)
- Audit logging for all actions
- Additional authentication checks
- Administrative dashboards and reports

Example:
```php
class AdminUserController
{
    public function index(Request $request): Response
    {
        $query = new GetUsersQuery(
            page: $request->getPage(),
            filters: $request->getFilters()
        );
        
        $users = $this->queryBus->handle($query);
        
        return $this->render('admin/users/index', [
            'users' => $users,
            'filters' => $request->getFilters()
        ]);
    }
}
```