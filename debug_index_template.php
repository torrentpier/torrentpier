<?php
/**
 * Debug Template Data for Index Page
 * 
 * Add this line in index.php right before the print_page('index.tpl'); call:
 * include('./debug_index_template.php');
 */

echo "<style>
body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 20px; }
.debug-section { background: white; margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
.debug-section h2 { color: #333; border-bottom: 2px solid #007cba; margin-top: 0; }
.debug-section h3 { color: #666; margin-top: 20px; }
.debug-section pre { background: #f8f8f8; padding: 10px; border: 1px solid #ddd; overflow: auto; max-height: 400px; font-size: 12px; }
.highlight { background-color: #ffffcc; }
.error { color: red; font-weight: bold; }
.success { color: green; font-weight: bold; }
</style>";

echo "<div class='debug-section'>";
echo "<h2>Index Page Template Debug Information</h2>";

// Get the current template instance
$template = template();

echo "<h3>Template Instance Information:</h3>";
echo "<pre>";
echo "Template class: " . get_class($template) . "\n";
echo "Template root: " . $template->root . "\n";
echo "Template name: " . $template->tpl . "\n";
echo "</pre>";

// Check if we have any categories
echo "<h3>Categories Data (c block):</h3>";
if (isset($template->_tpldata['c.'])) {
    echo "<pre>";
    echo "Number of categories: " . count($template->_tpldata['c.']) . "\n";
    foreach ($template->_tpldata['c.'] as $i => $cat) {
        echo "Category $i:\n";
        echo "  CAT_ID: " . ($cat['CAT_ID'] ?? 'NOT SET') . "\n";
        echo "  CAT_TITLE: " . ($cat['CAT_TITLE'] ?? 'NOT SET') . "\n";
        echo "  U_VIEWCAT: " . ($cat['U_VIEWCAT'] ?? 'NOT SET') . "\n";
        
        // Check for forums in this category
        if (isset($cat['f.'])) {
            echo "  Forums count: " . count($cat['f.']) . "\n";
            foreach ($cat['f.'] as $j => $forum) {
                echo "    Forum $j:\n";
                echo "      FORUM_ID: " . ($forum['FORUM_ID'] ?? 'NOT SET') . "\n";
                echo "      FORUM_NAME: " . ($forum['FORUM_NAME'] ?? 'NOT SET') . "\n";
                echo "      FORUM_DESC: " . (isset($forum['FORUM_DESC']) ? substr($forum['FORUM_DESC'], 0, 50) . '...' : 'NOT SET') . "\n";
                if (isset($forum['sf.'])) {
                    echo "      Subforums count: " . count($forum['sf.']) . "\n";
                }
            }
        } else {
            echo "  <span class='error'>No forums found in this category!</span>\n";
        }
        echo "\n";
    }
    echo "</pre>";
} else {
    echo "<pre><span class='error'>No categories found! The 'c.' block is missing from _tpldata</span></pre>";
}

// Check the raw _tpldata structure for debugging
echo "<h3>Raw _tpldata Structure (first level keys):</h3>";
echo "<pre>";
echo "Available blocks:\n";
foreach (array_keys($template->_tpldata) as $key) {
    $count = is_array($template->_tpldata[$key]) ? count($template->_tpldata[$key]) : 0;
    echo "  '$key' => $count items\n";
}
echo "</pre>";

// Check specific template variables
echo "<h3>Key Template Variables:</h3>";
echo "<pre>";
echo "SHOW_FORUMS: " . ($template->vars['SHOW_FORUMS'] ?? 'NOT SET') . "\n";
echo "PAGE_TITLE: " . ($template->vars['PAGE_TITLE'] ?? 'NOT SET') . "\n";
echo "NO_FORUMS_MSG: " . ($template->vars['NO_FORUMS_MSG'] ?? 'NOT SET') . "\n";
echo "TOTAL_TOPICS: " . ($template->vars['TOTAL_TOPICS'] ?? 'NOT SET') . "\n";
echo "TOTAL_POSTS: " . ($template->vars['TOTAL_POSTS'] ?? 'NOT SET') . "\n";
echo "</pre>";

// Check h_c block (hide categories)
echo "<h3>Hide Categories Block (h_c):</h3>";
if (isset($template->_tpldata['h_c.'])) {
    echo "<pre>";
    echo "Number of hide category items: " . count($template->_tpldata['h_c.']) . "\n";
    foreach ($template->_tpldata['h_c.'] as $i => $hc) {
        echo "Item $i:\n";
        echo "  H_C_ID: " . ($hc['H_C_ID'] ?? 'NOT SET') . "\n";
        echo "  H_C_TITLE: " . ($hc['H_C_TITLE'] ?? 'NOT SET') . "\n";
        echo "  H_C_CHEKED: " . ($hc['H_C_CHEKED'] ?? 'NOT SET') . "\n";
    }
    echo "</pre>";
} else {
    echo "<pre><span class='error'>No h_c block found!</span></pre>";
}

// Check if Twig is working properly
echo "<h3>Twig Environment Check:</h3>";
echo "<pre>";
if (method_exists($template, 'getTwig')) {
    $twig = $template->getTwig();
    echo "Twig class: " . get_class($twig) . "\n";
    echo "Twig loader: " . get_class($twig->getLoader()) . "\n";
    
    // Check template paths
    if (method_exists($twig->getLoader(), 'getPaths')) {
        echo "Template paths:\n";
        foreach ($twig->getLoader()->getPaths() as $path) {
            echo "  - $path\n";
        }
    }
    
    // Check if index.tpl exists
    $indexTpl = $template->root . '/index.tpl';
    echo "Index template file: $indexTpl\n";
    echo "Index template exists: " . (file_exists($indexTpl) ? 'YES' : 'NO') . "\n";
    
} else {
    echo "<span class='error'>No Twig environment available!</span>\n";
}
echo "</pre>";

echo "</div>";

die("DEBUG COMPLETE - Template structure shown above");
?>