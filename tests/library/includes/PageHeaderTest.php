<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Comprehensive unit tests for PageHeader class
 * Testing Framework: PHPUnit
 */
class PageHeaderTest extends TestCase
{
    private $pageHeader;
    private $mockRequest;
    private $mockResponse;
    private $mockConfig;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mocks for dependencies
        $this->mockRequest = $this->createMock(Request::class);
        $this->mockResponse = $this->createMock(Response::class);
        $this->mockConfig = $this->createMock(Config::class);
        
        // Initialize PageHeader with mocked dependencies
        $this->pageHeader = new PageHeader($this->mockRequest, $this->mockResponse, $this->mockConfig);
    }

    protected function tearDown(): void
    {
        $this->pageHeader = null;
        $this->mockRequest = null;
        $this->mockResponse = null;
        $this->mockConfig = null;
        parent::tearDown();
    }

    /**
     * Test basic header generation with default settings
     */
    public function testGenerateHeaderWithDefaults()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'Test Site'],
                ['site_description', 'Test Description'],
                ['theme', 'default']
            ]);

        $header = $this->pageHeader->generateHeader();
        
        $this->assertNotEmpty($header);
        $this->assertStringContainsString('Test Site', $header);
        $this->assertStringContainsString('Test Description', $header);
        $this->assertStringContainsString('<!DOCTYPE html>', $header);
    }

    /**
     * Test header generation with custom title
     */
    public function testGenerateHeaderWithCustomTitle()
    {
        $customTitle = 'Custom Page Title';
        
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'Test Site'],
                ['site_description', 'Test Description'],
                ['theme', 'default']
            ]);

        $header = $this->pageHeader->generateHeader($customTitle);
        
        $this->assertStringContainsString($customTitle, $header);
        $this->assertStringContainsString('Test Site', $header);
    }

    /**
     * Test header generation with empty title
     */
    public function testGenerateHeaderWithEmptyTitle()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'Test Site'],
                ['site_description', 'Test Description'],
                ['theme', 'default']
            ]);

        $header = $this->pageHeader->generateHeader('');
        
        $this->assertStringContainsString('Test Site', $header);
        $this->assertStringNotContainsString(' | ', $header);
    }

    /**
     * Test header generation with null title
     */
    public function testGenerateHeaderWithNullTitle()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'Test Site'],
                ['site_description', 'Test Description'],
                ['theme', 'default']
            ]);

        $header = $this->pageHeader->generateHeader(null);
        
        $this->assertStringContainsString('Test Site', $header);
        $this->assertNotEmpty($header);
    }

    /**
     * Test meta tag generation
     */
    public function testGenerateMetaTags()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_description', 'Test Description'],
                ['site_keywords', 'test,keywords,site'],
                ['site_author', 'Test Author']
            ]);

        $metaTags = $this->pageHeader->generateMetaTags();
        
        $this->assertStringContainsString('name="description"', $metaTags);
        $this->assertStringContainsString('Test Description', $metaTags);
        $this->assertStringContainsString('name="keywords"', $metaTags);
        $this->assertStringContainsString('test,keywords,site', $metaTags);
        $this->assertStringContainsString('name="author"', $metaTags);
        $this->assertStringContainsString('Test Author', $metaTags);
    }

    /**
     * Test meta tag generation with missing configuration
     */
    public function testGenerateMetaTagsWithMissingConfig()
    {
        $this->mockConfig->method('get')
            ->willReturn(null);

        $metaTags = $this->pageHeader->generateMetaTags();
        
        $this->assertStringContainsString('name="viewport"', $metaTags);
        $this->assertStringContainsString('charset=', $metaTags);
    }

    /**
     * Test CSS link generation
     */
    public function testGenerateCssLinks()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['theme', 'custom'],
                ['css_files', ['main.css', 'theme.css']],
                ['base_url', 'https://example.com']
            ]);

        $cssLinks = $this->pageHeader->generateCssLinks();
        
        $this->assertStringContainsString('rel="stylesheet"', $cssLinks);
        $this->assertStringContainsString('main.css', $cssLinks);
        $this->assertStringContainsString('theme.css', $cssLinks);
        $this->assertStringContainsString('https://example.com', $cssLinks);
    }

    /**
     * Test CSS link generation with empty CSS files
     */
    public function testGenerateCssLinksWithEmptyFiles()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['theme', 'default'],
                ['css_files', []],
                ['base_url', 'https://example.com']
            ]);

        $cssLinks = $this->pageHeader->generateCssLinks();
        
        $this->assertStringContainsString('rel="stylesheet"', $cssLinks);
        $this->assertStringContainsString('default', $cssLinks);
    }

    /**
     * Test JavaScript link generation
     */
    public function testGenerateJsLinks()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['js_files', ['main.js', 'utils.js']],
                ['base_url', 'https://example.com']
            ]);

        $jsLinks = $this->pageHeader->generateJsLinks();
        
        $this->assertStringContainsString('src=', $jsLinks);
        $this->assertStringContainsString('main.js', $jsLinks);
        $this->assertStringContainsString('utils.js', $jsLinks);
        $this->assertStringContainsString('https://example.com', $jsLinks);
    }

    /**
     * Test JavaScript link generation with no JS files
     */
    public function testGenerateJsLinksWithNoFiles()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['js_files', null],
                ['base_url', 'https://example.com']
            ]);

        $jsLinks = $this->pageHeader->generateJsLinks();
        
        $this->assertEmpty($jsLinks);
    }

    /**
     * Test canonical URL generation
     */
    public function testGenerateCanonicalUrl()
    {
        $this->mockRequest->method('getUri')
            ->willReturn('https://example.com/page');
        
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['canonical_url', true],
                ['base_url', 'https://example.com']
            ]);

        $canonical = $this->pageHeader->generateCanonicalUrl();
        
        $this->assertStringContainsString('rel="canonical"', $canonical);
        $this->assertStringContainsString('https://example.com/page', $canonical);
    }

    /**
     * Test canonical URL generation when disabled
     */
    public function testGenerateCanonicalUrlWhenDisabled()
    {
        $this->mockConfig->method('get')
            ->willReturn(false);

        $canonical = $this->pageHeader->generateCanonicalUrl();
        
        $this->assertEmpty($canonical);
    }

    /**
     * Test Open Graph meta tag generation
     */
    public function testGenerateOpenGraphTags()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['og_title', 'Test OG Title'],
                ['og_description', 'Test OG Description'],
                ['og_image', 'https://example.com/image.jpg'],
                ['og_url', 'https://example.com'],
                ['og_type', 'website']
            ]);

        $ogTags = $this->pageHeader->generateOpenGraphTags();
        
        $this->assertStringContainsString('property="og:title"', $ogTags);
        $this->assertStringContainsString('Test OG Title', $ogTags);
        $this->assertStringContainsString('property="og:description"', $ogTags);
        $this->assertStringContainsString('Test OG Description', $ogTags);
        $this->assertStringContainsString('property="og:image"', $ogTags);
        $this->assertStringContainsString('https://example.com/image.jpg', $ogTags);
    }

    /**
     * Test Twitter Card meta tag generation
     */
    public function testGenerateTwitterCardTags()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['twitter_card', 'summary'],
                ['twitter_site', '@testsite'],
                ['twitter_creator', '@testcreator'],
                ['twitter_title', 'Test Twitter Title'],
                ['twitter_description', 'Test Twitter Description'],
                ['twitter_image', 'https://example.com/twitter-image.jpg']
            ]);

        $twitterTags = $this->pageHeader->generateTwitterCardTags();
        
        $this->assertStringContainsString('name="twitter:card"', $twitterTags);
        $this->assertStringContainsString('summary', $twitterTags);
        $this->assertStringContainsString('name="twitter:site"', $twitterTags);
        $this->assertStringContainsString('@testsite', $twitterTags);
        $this->assertStringContainsString('name="twitter:creator"', $twitterTags);
        $this->assertStringContainsString('@testcreator', $twitterTags);
    }

    /**
     * Test favicon generation
     */
    public function testGenerateFavicon()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['favicon', 'favicon.ico'],
                ['base_url', 'https://example.com']
            ]);

        $favicon = $this->pageHeader->generateFavicon();
        
        $this->assertStringContainsString('rel="icon"', $favicon);
        $this->assertStringContainsString('favicon.ico', $favicon);
        $this->assertStringContainsString('https://example.com', $favicon);
    }

    /**
     * Test favicon generation with no favicon configured
     */
    public function testGenerateFaviconWithNone()
    {
        $this->mockConfig->method('get')
            ->willReturn(null);

        $favicon = $this->pageHeader->generateFavicon();
        
        $this->assertEmpty($favicon);
    }

    /**
     * Test complete header generation with all components
     */
    public function testGenerateCompleteHeader()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'Complete Test Site'],
                ['site_description', 'Complete Test Description'],
                ['theme', 'complete'],
                ['css_files', ['main.css']],
                ['js_files', ['main.js']],
                ['canonical_url', true],
                ['og_title', 'Test OG Title'],
                ['twitter_card', 'summary'],
                ['favicon', 'favicon.ico'],
                ['base_url', 'https://example.com']
            ]);

        $this->mockRequest->method('getUri')
            ->willReturn('https://example.com/complete');

        $header = $this->pageHeader->generateCompleteHeader('Complete Page');
        
        $this->assertStringContainsString('<!DOCTYPE html>', $header);
        $this->assertStringContainsString('Complete Page', $header);
        $this->assertStringContainsString('Complete Test Site', $header);
        $this->assertStringContainsString('rel="stylesheet"', $header);
        $this->assertStringContainsString('main.css', $header);
        $this->assertStringContainsString('main.js', $header);
        $this->assertStringContainsString('rel="canonical"', $header);
        $this->assertStringContainsString('property="og:title"', $header);
        $this->assertStringContainsString('name="twitter:card"', $header);
        $this->assertStringContainsString('rel="icon"', $header);
    }

    /**
     * Test header generation with XSS protection
     */
    public function testGenerateHeaderWithXssProtection()
    {
        $maliciousTitle = '<script>alert("xss")</script>';
        
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'Test Site'],
                ['site_description', 'Test Description'],
                ['theme', 'default']
            ]);

        $header = $this->pageHeader->generateHeader($maliciousTitle);
        
        $this->assertStringNotContainsString('<script>', $header);
        $this->assertStringNotContainsString('alert("xss")', $header);
        $this->assertStringContainsString('&lt;script&gt;', $header);
    }

    /**
     * Test header generation with special characters
     */
    public function testGenerateHeaderWithSpecialCharacters()
    {
        $specialTitle = 'Café & Résumé - "Quotes" & \'Apostrophes\'';
        
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'Test Site'],
                ['site_description', 'Test Description'],
                ['theme', 'default']
            ]);

        $header = $this->pageHeader->generateHeader($specialTitle);
        
        $this->assertStringContainsString($specialTitle, $header);
        $this->assertStringContainsString('Test Site', $header);
    }

    /**
     * Test header generation with very long title
     */
    public function testGenerateHeaderWithLongTitle()
    {
        $longTitle = str_repeat('Very Long Title ', 20);
        
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'Test Site'],
                ['site_description', 'Test Description'],
                ['theme', 'default']
            ]);

        $header = $this->pageHeader->generateHeader($longTitle);
        
        $this->assertStringContainsString($longTitle, $header);
        $this->assertNotEmpty($header);
    }

    /**
     * Test error handling when config is unavailable
     */
    public function testGenerateHeaderWithUnavailableConfig()
    {
        $this->mockConfig->method('get')
            ->willThrowException(new Exception('Config unavailable'));

        $header = $this->pageHeader->generateHeader('Test Title');
        
        $this->assertStringContainsString('Test Title', $header);
        $this->assertStringContainsString('<!DOCTYPE html>', $header);
    }

    /**
     * Test header generation with RTL language support
     */
    public function testGenerateHeaderWithRtlLanguage()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'موقع الاختبار'],
                ['site_description', 'وصف الاختبار'],
                ['language', 'ar'],
                ['text_direction', 'rtl'],
                ['theme', 'default']
            ]);

        $header = $this->pageHeader->generateHeader('صفحة الاختبار');
        
        $this->assertStringContainsString('dir="rtl"', $header);
        $this->assertStringContainsString('lang="ar"', $header);
        $this->assertStringContainsString('موقع الاختبار', $header);
        $this->assertStringContainsString('صفحة الاختبار', $header);
    }

    /**
     * Test header caching mechanism
     */
    public function testHeaderCaching()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['site_title', 'Cached Site'],
                ['site_description', 'Cached Description'],
                ['theme', 'default'],
                ['cache_headers', true]
            ]);

        $header1 = $this->pageHeader->generateHeader('Cached Page');
        $header2 = $this->pageHeader->generateHeader('Cached Page');
        
        $this->assertEquals($header1, $header2);
        $this->assertStringContainsString('Cached Page', $header1);
        $this->assertStringContainsString('Cached Site', $header1);
    }

    /**
     * Test responsive meta viewport tag
     */
    public function testResponsiveViewportTag()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['responsive', true],
                ['viewport_meta', 'width=device-width, initial-scale=1.0']
            ]);

        $metaTags = $this->pageHeader->generateMetaTags();
        
        $this->assertStringContainsString('name="viewport"', $metaTags);
        $this->assertStringContainsString('width=device-width', $metaTags);
        $this->assertStringContainsString('initial-scale=1.0', $metaTags);
    }

    /**
     * Test CSP (Content Security Policy) header generation
     */
    public function testContentSecurityPolicyHeader()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['csp_enabled', true],
                ['csp_policy', "default-src 'self'; script-src 'self' 'unsafe-inline'"]
            ]);

        $cspHeader = $this->pageHeader->generateCspHeader();
        
        $this->assertStringContainsString('Content-Security-Policy', $cspHeader);
        $this->assertStringContainsString("default-src 'self'", $cspHeader);
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline'", $cspHeader);
    }

    /**
     * Test structured data (JSON-LD) generation
     */
    public function testStructuredDataGeneration()
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['structured_data', true],
                ['site_name', 'Test Site'],
                ['site_url', 'https://example.com'],
                ['site_logo', 'https://example.com/logo.png']
            ]);

        $structuredData = $this->pageHeader->generateStructuredData();
        
        $this->assertStringContainsString('application/ld+json', $structuredData);
        $this->assertStringContainsString('@context', $structuredData);
        $this->assertStringContainsString('@type', $structuredData);
        $this->assertStringContainsString('Test Site', $structuredData);
        $this->assertStringContainsString('https://example.com', $structuredData);
    }
}