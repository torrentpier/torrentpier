<?php

namespace Tests\Templates;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Templates\DefaultPageHeaderTemplate;
use App\Models\Page;
use App\Models\User;
use App\Services\NavigationService;
use App\Services\ThemeService;
use App\Exceptions\TemplateException;

class DefaultPageHeaderTemplateTest extends TestCase
{
    private DefaultPageHeaderTemplate $template;
    private MockObject $navigationService;
    private MockObject $themeService;
    private MockObject $page;
    private MockObject $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->navigationService = $this->createMock(NavigationService::class);
        $this->themeService = $this->createMock(ThemeService::class);
        $this->page = $this->createMock(Page::class);
        $this->user = $this->createMock(User::class);
        
        $this->template = new DefaultPageHeaderTemplate(
            $this->navigationService,
            $this->themeService
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->template, $this->navigationService, $this->themeService, $this->page, $this->user);
    }

    /**
     * @test
     */
    public function it_renders_basic_header_with_page_title(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->page->method('getMetaDescription')->willReturn('Test description');
        $this->themeService->method('getCurrentTheme')->willReturn('default');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<title>Test Page</title>', $result);
        $this->assertStringContainsString('<meta name="description" content="Test description">', $result);
    }

    /**
     * @test
     */
    public function it_renders_header_with_user_authentication(): void
    {
        $this->page->method('getTitle')->willReturn('Dashboard');
        $this->user->method('getName')->willReturn('John Doe');
        $this->user->method('getEmail')->willReturn('john@example.com');
        $this->user->method('isAuthenticated')->willReturn(true);
        
        $result = $this->template->render($this->page, $this->user);
        
        $this->assertStringContainsString('John Doe', $result);
        $this->assertStringContainsString('john@example.com', $result);
    }

    /**
     * @test
     */
    public function it_renders_header_for_anonymous_user(): void
    {
        $this->page->method('getTitle')->willReturn('Public Page');
        $this->user->method('isAuthenticated')->willReturn(false);
        
        $result = $this->template->render($this->page, $this->user);
        
        $this->assertStringContainsString('Login', $result);
        $this->assertStringContainsString('Register', $result);
        $this->assertStringNotContainsString('Logout', $result);
    }

    /**
     * @test
     */
    public function it_includes_navigation_menu(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->navigationService->method('getMainMenu')->willReturn([
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'About', 'url' => '/about'],
            ['label' => 'Contact', 'url' => '/contact']
        ]);
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('Home', $result);
        $this->assertStringContainsString('About', $result);
        $this->assertStringContainsString('Contact', $result);
    }

    /**
     * @test
     */
    public function it_applies_theme_specific_styling(): void
    {
        $this->page->method('getTitle')->willReturn('Themed Page');
        $this->themeService->method('getCurrentTheme')->willReturn('dark');
        $this->themeService->method('getThemeStyles')->willReturn([
            'header-bg' => '#333333',
            'header-text' => '#ffffff'
        ]);
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('#333333', $result);
        $this->assertStringContainsString('#ffffff', $result);
        $this->assertStringContainsString('theme-dark', $result);
    }

    /**
     * @test
     */
    public function it_handles_empty_page_title(): void
    {
        $this->page->method('getTitle')->willReturn('');
        $this->page->method('getSlug')->willReturn('test-page');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<title>Untitled Page</title>', $result);
    }

    /**
     * @test
     */
    public function it_handles_null_page_title(): void
    {
        $this->page->method('getTitle')->willReturn(null);
        $this->page->method('getSlug')->willReturn('test-page');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<title>Untitled Page</title>', $result);
    }

    /**
     * @test
     */
    public function it_sanitizes_page_title_for_xss(): void
    {
        $this->page->method('getTitle')->willReturn('<script>alert("xss")</script>');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    /**
     * @test
     */
    public function it_includes_canonical_url(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->page->method('getCanonicalUrl')->willReturn('https://example.com/test-page');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<link rel="canonical" href="https://example.com/test-page">', $result);
    }

    /**
     * @test
     */
    public function it_includes_open_graph_meta_tags(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->page->method('getMetaDescription')->willReturn('Test description');
        $this->page->method('getOpenGraphImage')->willReturn('https://example.com/image.jpg');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<meta property="og:title" content="Test Page">', $result);
        $this->assertStringContainsString('<meta property="og:description" content="Test description">', $result);
        $this->assertStringContainsString('<meta property="og:image" content="https://example.com/image.jpg">', $result);
    }

    /**
     * @test
     */
    public function it_includes_twitter_meta_tags(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->page->method('getMetaDescription')->willReturn('Test description');
        $this->page->method('getTwitterImage')->willReturn('https://example.com/twitter-image.jpg');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<meta name="twitter:title" content="Test Page">', $result);
        $this->assertStringContainsString('<meta name="twitter:description" content="Test description">', $result);
        $this->assertStringContainsString('<meta name="twitter:image" content="https://example.com/twitter-image.jpg">', $result);
    }

    /**
     * @test
     */
    public function it_handles_long_page_titles(): void
    {
        $longTitle = str_repeat('Very Long Title ', 20);
        $this->page->method('getTitle')->willReturn($longTitle);
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<title>', $result);
        $this->assertLessThan(70, strlen($this->extractTitleFromHtml($result)));
    }

    /**
     * @test
     */
    public function it_includes_responsive_meta_viewport(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<meta name="viewport" content="width=device-width, initial-scale=1">', $result);
    }

    /**
     * @test
     */
    public function it_includes_charset_meta_tag(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<meta charset="UTF-8">', $result);
    }

    /**
     * @test
     */
    public function it_handles_special_characters_in_title(): void
    {
        $this->page->method('getTitle')->willReturn('Test & Page "with" quotes');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('Test &amp; Page &quot;with&quot; quotes', $result);
    }

    /**
     * @test
     */
    public function it_includes_breadcrumb_navigation(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->navigationService->method('getBreadcrumbs')->willReturn([
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Category', 'url' => '/category'],
            ['label' => 'Test Page', 'url' => '/category/test-page']
        ]);
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('breadcrumb', $result);
        $this->assertStringContainsString('Home', $result);
        $this->assertStringContainsString('Category', $result);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_page_is_null(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Page cannot be null');
        
        $this->template->render(null);
    }

    /**
     * @test
     */
    public function it_handles_navigation_service_failure(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->navigationService->method('getMainMenu')->willThrowException(new \Exception('Navigation service unavailable'));
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<title>Test Page</title>', $result);
        $this->assertStringNotContainsString('Navigation service unavailable', $result);
    }

    /**
     * @test
     */
    public function it_handles_theme_service_failure(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->themeService->method('getCurrentTheme')->willThrowException(new \Exception('Theme service unavailable'));
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<title>Test Page</title>', $result);
        $this->assertStringContainsString('theme-default', $result);
    }

    /**
     * @test
     */
    public function it_includes_csrf_token_in_forms(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->user->method('isAuthenticated')->willReturn(true);
        
        $result = $this->template->render($this->page, $this->user);
        
        $this->assertStringContainsString('csrf_token', $result);
        $this->assertStringContainsString('name="_token"', $result);
    }

    /**
     * @test
     */
    public function it_includes_language_attributes(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->page->method('getLanguage')->willReturn('en');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('lang="en"', $result);
        $this->assertStringContainsString('hreflang="en"', $result);
    }

    /**
     * @test
     */
    public function it_includes_search_functionality(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('search', $result);
        $this->assertStringContainsString('type="search"', $result);
    }

    /**
     * @test
     */
    public function it_handles_mobile_specific_rendering(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15';
        
        $this->page->method('getTitle')->willReturn('Test Page');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('mobile-header', $result);
        $this->assertStringContainsString('hamburger-menu', $result);
        
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * @test
     */
    public function it_renders_with_custom_css_classes(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        $this->page->method('getCssClasses')->willReturn(['custom-class', 'another-class']);
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('custom-class', $result);
        $this->assertStringContainsString('another-class', $result);
    }

    /**
     * @test
     */
    public function it_includes_accessibility_attributes(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('role="banner"', $result);
        $this->assertStringContainsString('aria-label', $result);
        $this->assertStringContainsString('skip-to-content', $result);
    }

    /**
     * @test
     */
    public function it_performance_with_large_navigation_menu(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        
        $largeMenu = [];
        for ($i = 1; $i <= 100; $i++) {
            $largeMenu[] = ['label' => "Item $i", 'url' => "/item-$i"];
        }
        $this->navigationService->method('getMainMenu')->willReturn($largeMenu);
        
        $startTime = microtime(true);
        $result = $this->template->render($this->page);
        $endTime = microtime(true);
        
        $this->assertLessThan(0.1, $endTime - $startTime, 'Template rendering should be fast even with large menus');
        $this->assertStringContainsString('Item 1', $result);
        $this->assertStringContainsString('Item 100', $result);
    }

    /**
     * @test
     */
    public function it_handles_unicode_characters_in_title(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page with émojis 🚀 and unicode ñ');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('émojis 🚀 and unicode ñ', $result);
    }

    /**
     * @test
     */
    public function it_generates_valid_html5_markup(): void
    {
        $this->page->method('getTitle')->willReturn('Test Page');
        
        $result = $this->template->render($this->page);
        
        $this->assertStringContainsString('<!DOCTYPE html>', $result);
        $this->assertStringContainsString('<html', $result);
        $this->assertStringContainsString('<head>', $result);
        $this->assertStringContainsString('</head>', $result);
        $this->assertStringContainsString('<body', $result);
    }

    /**
     * Helper method to extract title from HTML string
     */
    private function extractTitleFromHtml(string $html): string
    {
        if (preg_match('/<title>(.*?)<\/title>/', $html, $matches)) {
            return $matches[1];
        }
        return '';
    }
}