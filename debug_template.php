<?php
/**
 * Template Debug Helper
 * 
 * Usage:
 * 1. Add this line anywhere in your index.php or other page after the template is set up:
 *    include('./debug_template.php');
 * 
 * 2. Or call the debug methods directly:
 *    template()->debugDump('Index Page Template Debug', true);
 *    template()->debugBlock('catrow');
 */

// Get the current template instance
$template = template();

echo "<style>
body { font-family: Arial, sans-serif; }
h2 { color: #333; border-bottom: 2px solid #ccc; }
h3 { color: #666; margin-top: 20px; }
pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow: auto; max-height: 400px; }
</style>";

echo "<h1>Template Debug Information</h1>";

// Dump all template data
$template->debugDump('Complete Template State');

// Check specifically for forum-related blocks
$forumBlocks = ['catrow', 'forumrow', 'category', 'forum'];
foreach ($forumBlocks as $block) {
    if (isset($template->_tpldata[$block . '.'])) {
        $template->debugBlock($block);
    }
}

// Show what template files are loaded
echo "<h3>Current template root:</h3>";
echo "<pre>" . $template->root . "</pre>";

echo "<h3>Template instance class:</h3>";
echo "<pre>" . get_class($template) . "</pre>";

echo "<h3>Twig environment info:</h3>";
if (method_exists($template, 'getTwig')) {
    $twig = $template->getTwig();
    echo "<pre>";
    echo "Twig loader: " . get_class($twig->getLoader()) . "\n";
    if (method_exists($twig->getLoader(), 'getPaths')) {
        echo "Template paths: " . implode(', ', $twig->getLoader()->getPaths()) . "\n";
    }
    echo "</pre>";
} else {
    echo "<pre>No Twig environment available</pre>";
}

die();
?>