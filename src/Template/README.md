# TorrentPier Template System (Twig-based)

The TorrentPier Template system has been modernized to use Twig internally while maintaining **100% backward compatibility** with the existing template syntax and API.

## Overview

The new Template system provides:
- **Modern Twig engine** internally for better performance and features
- **Full backward compatibility** with existing `.tpl` files and API
- **Automatic syntax conversion** from legacy syntax to Twig
- **Singleton pattern** consistent with other TorrentPier services
- **Extensible architecture** with clean separation of concerns

## Architecture

```
src/Template/
├── Template.php                    # Main singleton class
├── TwigEnvironmentFactory.php      # Twig environment setup
├── Extensions/                     # Twig extensions for compatibility
│   ├── LegacySyntaxExtension.php  # Legacy syntax conversion
│   ├── BlockExtension.php         # Block system support
│   └── LanguageExtension.php      # Language system integration
├── Loaders/                       # Template loaders
│   └── LegacyTemplateLoader.php   # Legacy template loading
└── README.md                      # This documentation
```

## Usage

### Basic Usage (Backward Compatible)

All existing code continues to work unchanged:

```php
// Original usage - still works
$template = new TorrentPier\Legacy\Template($template_dir);
$template->assign_vars(['TITLE' => 'My Page']);
$template->set_filenames(['body' => 'index.tpl']);
$template->pparse('body');
```

### New Singleton Usage (Recommended)

```php
// New singleton approach
$template = template(); // or TorrentPier\Template\Template::getInstance()
$template->assign_vars(['TITLE' => 'My Page']);
$template->set_filenames(['body' => 'index.tpl']);
$template->pparse('body');

// Directory-specific instance
$template = template('/path/to/templates');
```

### Advanced Twig Features

```php
// Access Twig environment for advanced features
$twig = template()->getTwig();

// Add custom filters, functions, etc.
$twig->addFilter(new \Twig\TwigFilter('my_filter', 'my_filter_function'));
```

## Template Syntax Conversion

The system automatically converts legacy template syntax to Twig:

### Variables

```twig
<!-- Legacy syntax (still works) -->
{TITLE}
{L_WELCOME}
{$user.username}
{#CONSTANT#}

<!-- Converted to Twig internally -->
{{ V.TITLE }}
{{ L.WELCOME }}
{{ user.username }}
{{ constant('CONSTANT') }}
```

### Conditionals

```twig
<!-- Legacy syntax -->
<!-- IF CONDITION -->Content<!-- ENDIF -->
<!-- IF CONDITION -->If content<!-- ELSE -->Else content<!-- ENDIF -->

<!-- Converted to Twig -->
{% if CONDITION %}Content{% endif %}
{% if CONDITION %}If content{% else %}Else content{% endif %}
```

### Blocks/Loops

```twig
<!-- Legacy syntax -->
<!-- BEGIN items -->
    {items.NAME}: {items.VALUE}
<!-- END items -->

<!-- Converted to Twig -->
{% for items_item in _tpldata['items.']|default([]) %}
    {{ items_item.NAME }}: {{ items_item.VALUE }}
{% endfor %}
```

### Includes

```twig
<!-- Legacy syntax -->
<!-- INCLUDE header.tpl -->

<!-- Converted to Twig -->
{{ include('header.tpl') }}
```

## New Features

### Enhanced Template Functions

```php
// Check if template exists before including
if (template()->getTwig()->getLoader()->exists('optional.tpl')) {
    // Include template
}

// Use Twig's powerful features
$template->getTwig()->addGlobal('current_user', $current_user);
```

### Better Error Handling

- Detailed error messages with line numbers
- Template debugging information
- Graceful fallback to legacy system if needed

### Performance Improvements

- Twig's compiled template caching
- Automatic template recompilation on changes
- Optimized template rendering

## Migration Guide

### For Developers

**No changes required!** All existing code continues to work. However, you can optionally:

1. **Use the singleton pattern:**
   ```php
   // Old
   $template = new TorrentPier\Legacy\Template($dir);

   // New (recommended)
   $template = template($dir);
   ```

2. **Leverage new Twig features:**
   ```php
   // Add custom functionality
   template()->getTwig()->addFunction(new \Twig\TwigFunction('my_func', 'my_function'));
   ```

### For Template Designers

Templates continue to work with the existing syntax. New templates can optionally use:

1. **Modern Twig syntax** for new features
2. **Mixed syntax** (legacy + Twig) in the same template
3. **Twig inheritance** for better template organization

### Backward Compatibility

The system maintains 100% compatibility with:
- ✅ All existing `.tpl` files
- ✅ Legacy syntax (`{VARIABLE}`, `<!-- IF -->`, `<!-- BEGIN -->`)
- ✅ All Template class methods
- ✅ Block assignment (`assign_block_vars`)
- ✅ Variable assignment (`assign_vars`, `assign_var`)
- ✅ Template compilation and caching
- ✅ File includes and preprocessing

## Configuration

### Environment Setup

The system automatically configures Twig based on TorrentPier settings:

```php
// Debug mode based on dev level
'debug' => dev()->get_level() > 0,

// Cache based on template cache settings
'cache' => config()->get('xs_use_cache'),

// Backward compatibility settings
'strict_variables' => false,
'autoescape' => false,
```

### Extensions

All TorrentPier-specific functionality is provided through Twig extensions:

- **LegacySyntaxExtension**: Converts legacy syntax
- **BlockExtension**: Handles the `_tpldata` block system
- **LanguageExtension**: Integrates with the language system

## Troubleshooting

### Template Not Found

```php
// Check if template exists
if (!template()->getTwig()->getLoader()->exists('template.tpl')) {
    // Handle missing template
}
```

### Syntax Errors

The system provides detailed error messages for template syntax issues:

```
Twig\Error\SyntaxError: Unexpected token "punctuation" of value "}" in "template.tpl" at line 15.
```

### Fallback Mode

If Twig fails to render a template, the system automatically falls back to the legacy parser:

```php
// Automatic fallback on Twig failure
catch (\Exception $e) {
    return $this->legacyParse($handle);
}
```

## Examples

### Complex Template with Mixed Syntax

```twig
<!-- header.tpl -->
<header>
    <h1>{SITE_NAME}</h1>

    <!-- IF LOGGED_IN -->
        <p>Welcome, {USERNAME}!</p>

        <!-- BEGIN notifications -->
            <div class="notification">
                {notifications.MESSAGE}
                <span class="time">{{ notifications.TIMESTAMP|bb_date }}</span>
            </div>
        <!-- END notifications -->
    <!-- ELSE -->
        <a href="{LOGIN_URL}">Login</a>
    <!-- ENDIF -->
</header>
```

### Using Twig Features in Templates

```twig
<!-- Modern Twig syntax can be mixed with legacy -->
{% set user_count = users|length %}
<p>Total users: {{ user_count }}</p>

<!-- Legacy blocks still work -->
<!-- BEGIN users -->
    <div class="user">
        Name: {users.NAME}
        Joined: {{ users.JOIN_DATE|bb_date('d-M-Y') }}
    </div>
<!-- END users -->
```

## Performance

The new system provides significant performance improvements:

- **Template compilation**: Twig compiles templates to optimized PHP code
- **Intelligent caching**: Only recompiles when source templates change
- **Memory efficiency**: Reduced memory usage compared to legacy system
- **Faster rendering**: Compiled templates execute faster than interpreted ones

## Future Enhancements

Planned improvements include:

1. **Template inheritance**: Use Twig's `{% extends %}` for better template organization
2. **Macro system**: Reusable template components
3. **Advanced filters**: More built-in filters for common operations
4. **IDE support**: Better syntax highlighting and auto-completion
5. **Asset management**: Integration with modern asset pipelines

## Conclusion

The new Template system provides a modern, powerful foundation while preserving complete backward compatibility. Developers can continue using existing code while gradually adopting new features as needed.