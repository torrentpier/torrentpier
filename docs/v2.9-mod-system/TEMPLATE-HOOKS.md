# Template Hooks API

This document describes the template hook system that allows mods to integrate with TorrentPier's template rendering engine.

## Overview

The template system provides five strategic hooks where mods can inject functionality:

1. **`template_before_assign_vars`** - Modify or add template variables before assignment
2. **`template_before_compile`** - Modify template source code before compilation
3. **`template_after_compile`** - Modify compiled PHP code after compilation
4. **`template_before_render`** - Execute code before template rendering
5. **`template_after_render`** - Modify final HTML output after rendering

## Hook Registration

### Basic Registration

```php
// In your mod's init() method
public function init(): void
{
    $template = $GLOBALS['template'] ?? null;

    if ($template instanceof \TorrentPier\Legacy\Template) {
        $template->registerHook('template_after_render', [$this, 'injectCustomCode']);
    }
}

public function injectCustomCode($output)
{
    return $output . '<!-- Injected by MyMod -->';
}
```

### Hook with Priority

Lower priority numbers execute first (default is 10):

```php
$template->registerHook(
    'template_before_assign_vars',
    [$this, 'addVariables'],
    5  // Execute before hooks with priority 10
);
```

## Available Hooks

### 1. `template_before_assign_vars`

**When**: Before variables are assigned to template
**Receives**: Array of variables to be assigned
**Returns**: Modified array of variables
**Use Case**: Inject additional template variables or modify existing ones

```php
$template->registerHook('template_before_assign_vars', function($vars) {
    // Add new variables
    $vars['USER_KARMA'] = $this->getUserKarma();
    $vars['USER_KARMA_LEVEL'] = $this->getKarmaLevel();

    // Modify existing variables
    if (isset($vars['USERNAME'])) {
        $vars['USERNAME'] = $this->addBadges($vars['USERNAME']);
    }

    return $vars;
});
```

**Example Template Usage**:
```html
<!-- In template file -->
<div class="user-info">
    <span class="username">{USERNAME}</span>
    <span class="karma">Karma: {USER_KARMA} ({USER_KARMA_LEVEL})</span>
</div>
```

### 2. `template_before_compile`

**When**: Before template source is compiled to PHP
**Receives**: Template source code (string)
**Returns**: Modified template source code
**Use Case**: Add custom template tags, inject template blocks, modify template structure

```php
$template->registerHook('template_before_compile', function($code) {
    // Replace custom tags with standard tags
    $code = str_replace(
        '<!-- KARMA_WIDGET -->',
        '<!-- INCLUDE karma_widget.html -->',
        $code
    );

    // Inject additional template content
    $code = str_replace(
        '</head>',
        '<link rel="stylesheet" href="{TEMPLATE}karma.css"></head>',
        $code
    );

    return $code;
});
```

### 3. `template_after_compile`

**When**: After template is compiled to PHP code
**Receives**: Compiled PHP code (string)
**Returns**: Modified compiled code
**Use Case**: Advanced modifications to compiled templates (rarely needed)

```php
$template->registerHook('template_after_compile', function($compiled) {
    // Add PHP comment to compiled templates
    return "<?php /* Compiled with ModSystem v2.0 */ ?>\n" . $compiled;
});
```

⚠️ **Warning**: This is an advanced hook. Modifying compiled PHP requires deep understanding of the template compiler.

### 4. `template_before_render`

**When**: Before template rendering begins
**Receives**: Array with `handle` (template name) and `template` (Template instance)
**Returns**: Data (usually ignored, use for side effects)
**Use Case**: Execute code before rendering, set up state, log template usage

```php
$template->registerHook('template_before_render', function($data) {
    $handle = $data['handle'];
    $template = $data['template'];

    // Log template rendering for analytics
    if ($handle === 'viewtopic') {
        $this->logTemplateView('topic', $template->vars['TOPIC_ID'] ?? 0);
    }

    // Add last-minute variables
    $template->assign_var('MOD_VERSION', '2.0.0');

    return $data;
});
```

### 5. `template_after_render`

**When**: After template rendering completes
**Receives**: Final HTML output (string)
**Returns**: Modified HTML output
**Use Case**: Inject JavaScript, add analytics, modify final HTML

```php
$template->registerHook('template_after_render', function($output) {
    // Inject analytics code before closing body tag
    $analytics = '<script src="/mods/analytics/tracker.js"></script>';
    $output = str_replace('</body>', $analytics . '</body>', $output);

    // Add notification bar after body opening
    $notifications = '<div class="mod-notifications">' .
                     $this->getNotifications() .
                     '</div>';
    $output = str_replace('<body>', '<body>' . $notifications, $output);

    return $output;
});
```

## Complete Example: Karma Mod

```php
namespace Mods\KarmaSystem;

use TorrentPier\ModSystem\AbstractMod;

class Mod extends AbstractMod
{
    protected function init(): void
    {
        // Get global template instance
        $template = $GLOBALS['template'] ?? null;

        if (!($template instanceof \TorrentPier\Legacy\Template)) {
            return;
        }

        // Hook 1: Add karma variables to all templates
        $template->registerHook(
            'template_before_assign_vars',
            [$this, 'addKarmaVariables'],
            10
        );

        // Hook 2: Inject karma CSS into page head
        $template->registerHook(
            'template_after_render',
            [$this, 'injectKarmaAssets'],
            10
        );
    }

    public function addKarmaVariables(array $vars): array
    {
        // Only add karma vars if user is logged in
        if (!empty($vars['USER_LOGGED_IN'])) {
            $userId = $vars['USER_ID'] ?? 0;

            $vars['USER_KARMA'] = $this->getKarma($userId);
            $vars['USER_KARMA_LEVEL'] = $this->getKarmaLevel($userId);
            $vars['USER_KARMA_RANK'] = $this->getKarmaRank($userId);
        }

        return $vars;
    }

    public function injectKarmaAssets(string $output): string
    {
        // Inject karma CSS
        $css = '<link rel="stylesheet" href="/mods/karma-system/assets/karma.css">';
        $output = str_replace('</head>', $css . '</head>', $output);

        // Inject karma JavaScript
        $js = '<script src="/mods/karma-system/assets/karma.js"></script>';
        $output = str_replace('</body>', $js . '</body>', $output);

        return $output;
    }

    private function getKarma(int $userId): int
    {
        // Implementation...
        return 0;
    }

    private function getKarmaLevel(int $userId): string
    {
        // Implementation...
        return 'Newbie';
    }

    private function getKarmaRank(int $userId): int
    {
        // Implementation...
        return 1;
    }
}
```

## Hook Execution Order

Hooks execute in this order during template rendering:

```
1. Template file loaded
2. template_before_compile (modify source)
3. Compilation to PHP
4. template_after_compile (modify compiled code)
5. template_before_render (setup before rendering)
6. Variable assignment
   └─ template_before_assign_vars (modify variables)
7. Template execution
8. template_after_render (modify final output)
9. Output sent to browser
```

## Best Practices

### ✅ Do

- **Check template instance exists** before registering hooks
- **Return modified data** from hooks that expect it
- **Use appropriate hook** for your use case
- **Add error handling** in hook callbacks
- **Document your hooks** in mod README
- **Test hook behavior** thoroughly

### ❌ Don't

- **Don't modify template structure** in `after_render` hook (use `before_compile` instead)
- **Don't perform expensive operations** in hooks (cache when possible)
- **Don't throw exceptions** from hooks (they're caught but break the hook chain)
- **Don't rely on execution order** between mods (use priorities)
- **Don't modify other mods' injected content** without coordination

## Error Handling

Hook callbacks are wrapped in try-catch blocks to prevent breaking template rendering:

```php
$template->registerHook('template_after_render', function($output) {
    try {
        // Your code here
        return $this->modifyOutput($output);
    } catch (\Exception $e) {
        // Log error for debugging
        error_log("Karma mod template hook error: " . $e->getMessage());

        // Return original output to prevent breaking page
        return $output;
    }
});
```

Errors are automatically logged to `template_hooks` log file if `bb_log()` function is available.

## Performance Considerations

1. **Minimize hook callbacks**: Each hook adds overhead
2. **Cache expensive operations**: Don't recalculate same data for every template
3. **Use specific hooks**: Don't use `after_render` if `before_assign_vars` is sufficient
4. **Lazy load resources**: Only inject CSS/JS when needed
5. **Profile your hooks**: Use profiling to identify bottlenecks

## Debugging

Enable hook debugging:

```php
// In your mod's config
$this->config->set('debug_template_hooks', true);

// In hook callback
$template->registerHook('template_before_render', function($data) {
    if ($this->config->get('debug_template_hooks')) {
        error_log("Rendering template: " . $data['handle']);
    }
    return $data;
});
```

## Migration from Legacy Hooks

If your mod used legacy template modification methods:

| Legacy Method | New Hook |
|--------------|----------|
| `$template->assign_vars()` modification | `template_before_assign_vars` |
| Manual template file editing | `template_before_compile` |
| Output buffering (`ob_start`) | `template_after_render` |

## See Also

- [ModLoader API](MODLOADER-API.md) - Core mod system API
- [AbstractMod API](ABSTRACTMOD-API.md) - Base mod class API
- [Mod Development Guide](MOD-DEVELOPMENT-GUIDE.md) - Complete mod development guide
