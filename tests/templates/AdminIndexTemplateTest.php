<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Comprehensive unit tests for AdminIndexTemplate
 * 
 * Tests cover happy paths, edge cases, and failure conditions
 * Following PHPUnit best practices for clean, readable, and maintainable tests
 */
class AdminIndexTemplateTest extends TestCase
{
    private $adminIndexTemplate;
    private $mockRequest;
    private $mockResponse;
    private $mockDatabase;
    private $mockAuth;

    /**
     * Set up test fixtures before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock dependencies
        $this->mockRequest = $this->createMock(Request::class);
        $this->mockResponse = $this->createMock(Response::class);
        $this->mockDatabase = $this->createMock(Database::class);
        $this->mockAuth = $this->createMock(Auth::class);
        
        // Initialize the template with mocked dependencies
        $this->adminIndexTemplate = new AdminIndexTemplate(
            $this->mockRequest,
            $this->mockResponse,
            $this->mockDatabase,
            $this->mockAuth
        );
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        $this->adminIndexTemplate = null;
        parent::tearDown();
    }

    /**
     * Test successful template rendering with valid admin user
     */
    public function testRenderWithValidAdminUser(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn([
                'total_users' => 100,
                'active_sessions' => 15,
                'pending_reviews' => 5
            ]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString('Admin Dashboard', $result);
        $this->assertStringContainsString('100', $result); // total users
        $this->assertStringContainsString('15', $result);  // active sessions
        $this->assertStringContainsString('5', $result);   // pending reviews
    }

    /**
     * Test template rendering fails with unauthorized user
     */
    public function testRenderWithUnauthorizedUser(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('user', ['admin' => false]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);

        // Act & Assert
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Access denied: Admin privileges required');
        
        $this->adminIndexTemplate->render();
    }

    /**
     * Test template rendering with null/guest user
     */
    public function testRenderWithNullUser(): void
    {
        // Arrange
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn(null);

        // Act & Assert
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Access denied: Authentication required');
        
        $this->adminIndexTemplate->render();
    }

    /**
     * Test template rendering with database connection failure
     */
    public function testRenderWithDatabaseFailure(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willThrowException(new DatabaseException('Connection failed'));

        // Act & Assert
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Connection failed');
        
        $this->adminIndexTemplate->render();
    }

    /**
     * Test template rendering with empty/invalid database response
     */
    public function testRenderWithEmptyDatabaseResponse(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn([]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString('Admin Dashboard', $result);
        $this->assertStringContainsString('0', $result); // Should show zero for missing stats
    }

    /**
     * Test template rendering with malformed database response
     */
    public function testRenderWithMalformedDatabaseResponse(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn(['invalid' => 'data']);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString('Admin Dashboard', $result);
        // Should gracefully handle missing expected keys
    }

    /**
     * Test template CSS and JavaScript inclusion
     */
    public function testRenderIncludesAssets(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn(['total_users' => 50]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertStringContainsString('<link', $result);
        $this->assertStringContainsString('admin.css', $result);
        $this->assertStringContainsString('<script', $result);
        $this->assertStringContainsString('admin.js', $result);
    }

    /**
     * Test template meta tags and title
     */
    public function testRenderIncludesMetaTags(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn(['total_users' => 25]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertStringContainsString('<title>Admin Dashboard</title>', $result);
        $this->assertStringContainsString('<meta charset="utf-8">', $result);
        $this->assertStringContainsString('<meta name="viewport"', $result);
        $this->assertStringContainsString('content="width=device-width"', $result);
    }

    /**
     * Test template navigation elements
     */
    public function testRenderIncludesNavigation(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn(['total_users' => 75]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertStringContainsString('<nav', $result);
        $this->assertStringContainsString('Users', $result);
        $this->assertStringContainsString('Settings', $result);
        $this->assertStringContainsString('Reports', $result);
        $this->assertStringContainsString('Logout', $result);
    }

    /**
     * Test template with extremely large database values
     */
    public function testRenderWithLargeStatValues(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn([
                'total_users' => 999999999,
                'active_sessions' => 50000,
                'pending_reviews' => 10000
            ]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString('999,999,999', $result); // Should format large numbers
    }

    /**
     * Test template with negative database values
     */
    public function testRenderWithNegativeStatValues(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn([
                'total_users' => -5,
                'active_sessions' => -1,
                'pending_reviews' => -10
            ]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertIsString($result);
        // Should handle negative values gracefully, possibly converting to 0
        $this->assertStringNotContainsString('-5', $result);
    }

    /**
     * Test template XSS prevention
     */
    public function testRenderPreventsXSS(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin<script>alert("xss")</script>', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn(['total_users' => 100]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertIsString($result);
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result); // Should be escaped
    }

    /**
     * Test template caching functionality
     */
    public function testRenderWithCaching(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->exactly(2))
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once()) // Should only call once due to caching
            ->method('getAdminStats')
            ->willReturn(['total_users' => 100]);

        // Act
        $result1 = $this->adminIndexTemplate->render();
        $result2 = $this->adminIndexTemplate->render();

        // Assert
        $this->assertEquals($result1, $result2);
    }

    /**
     * Test template rendering with custom theme
     */
    public function testRenderWithCustomTheme(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true, 'theme' => 'dark']);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn(['total_users' => 100]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertStringContainsString('theme-dark', $result);
    }

    /**
     * Test template accessibility features
     */
    public function testRenderIncludesAccessibilityFeatures(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn(['total_users' => 100]);

        // Act
        $result = $this->adminIndexTemplate->render();

        // Assert
        $this->assertStringContainsString('aria-label', $result);
        $this->assertStringContainsString('role=', $result);
        $this->assertStringContainsString('alt=', $result);
    }

    /**
     * Helper method to create mock user objects
     */
    private function createMockUser(string $username, array $attributes = []): MockObject
    {
        $mockUser = $this->createMock(User::class);
        
        $mockUser->expects($this->any())
            ->method('getUsername')
            ->willReturn($username);
            
        $mockUser->expects($this->any())
            ->method('hasRole')
            ->with('admin')
            ->willReturn($attributes['admin'] ?? false);
            
        $mockUser->expects($this->any())
            ->method('getTheme')
            ->willReturn($attributes['theme'] ?? 'default');
            
        return $mockUser;
    }

    /**
     * Test template rendering performance with large datasets
     */
    public function testRenderPerformanceWithLargeDataset(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->once())
            ->method('getAdminStats')
            ->willReturn(['total_users' => 1000000]);

        // Act
        $startTime = microtime(true);
        $result = $this->adminIndexTemplate->render();
        $endTime = microtime(true);

        // Assert
        $this->assertIsString($result);
        $this->assertLessThan(1.0, $endTime - $startTime); // Should render in under 1 second
    }

    /**
     * Test template error handling with invalid template file
     */
    public function testRenderWithMissingTemplateFile(): void
    {
        // Arrange
        $adminIndexTemplate = new AdminIndexTemplate(
            $this->mockRequest,
            $this->mockResponse,
            $this->mockDatabase,
            $this->mockAuth,
            '/nonexistent/template.php' // Invalid template path
        );
        
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($mockUser);

        // Act & Assert
        $this->expectException(TemplateNotFoundException::class);
        $adminIndexTemplate->render();
    }

    /**
     * Test template rendering with concurrent access
     */
    public function testRenderWithConcurrentAccess(): void
    {
        // Arrange
        $mockUser = $this->createMockUser('admin', ['admin' => true]);
        $this->mockAuth->expects($this->exactly(3))
            ->method('getCurrentUser')
            ->willReturn($mockUser);
        
        $this->mockDatabase->expects($this->exactly(3))
            ->method('getAdminStats')
            ->willReturn(['total_users' => 100]);

        // Act - Simulate concurrent rendering
        $results = [];
        for ($i = 0; $i < 3; $i++) {
            $results[] = $this->adminIndexTemplate->render();
        }

        // Assert
        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertIsString($result);
            $this->assertStringContainsString('Admin Dashboard', $result);
        }
    }
}