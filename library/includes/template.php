<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Reserved prefixes:
 *
 *  "L_" - lang var, {L_VAR} is eq to $lang['VAR']
 *  "$"  - php var,  {$VAR}  is eq to $VAR (in $this->execute() scope!)
 *  "#"  - constant, {#CON}  is eq to CON
 *
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Template system constants
define('XS_TPL_PREFIX', 'tpl_');
define('XS_USE_ISSET', '1');
define('XS_TAG_NONE', 0);
define('XS_TAG_BEGIN', 2);
define('XS_TAG_END', 3);
define('XS_TAG_INCLUDE', 4);
define('XS_TAG_IF', 5);
define('XS_TAG_ELSE', 6);
define('XS_TAG_ELSEIF', 7);
define('XS_TAG_ENDIF', 8);
define('XS_TAG_BEGINELSE', 11);

class template
{
    // Variable that holds all the data we'll be substituting into the compiled templates
    // This will end up being a multi-dimensional array like this: $this->_tpldata[block.][iteration#][child.][iteration#][child2.][iteration#][variablename] == value
    // If it's a root-level variable, it'll be like this: $this->vars[varname] == value  or  $this->_tpldata['.'][0][varname] == value
    // Array "vars" is added for easier access to data
    public $_tpldata = array('.' => array(0 => array()));
    public $vars;

    // Hash of filenames for each template handle
    public $files = array();
    public $files_cache = array(); // array of cache files that exists
    public $files_cache2 = array(); // array of cache files (exists or not exists)

    // Root template directory
    public $root = '';

    // Cache directory
    public $cachedir = CACHE_DIR;

    // Template root directory
    public $tpldir = '';

    // Default template directory
    public $tpldef = 'default';

    // This will hash handle names to the compiled code for that handle
    public $compiled_code = array();

    // This will hold the uncompiled code for that handle.
    public $uncompiled_code = array();

    // Cache settings
    public $use_cache = 1;
    public $cache_writable = 1;

    // Auto-compile setting
    public $auto_compile = 1;

    // Current template name
    public $tpl = '';
    public $cur_tpl = '';

    // List of replacements (tpl files in this list will be replaced with other tpl files)
    public $replace = array();

    // Counter for include
    public $include_count = 0;

    // Extension tpl-cache files
    public $cached_tpl_ext = 'php';

    // eXtreme Styles variables
    public $xs_started = 0;

    // These handles will be parsed if pparse() is executed
    // Can be used to automatically include header/footer if there is any content
    public $preparse = '';
    public $postparse = '';

    public $lang = array();

    /**
     * Constructor. Installs XS mod on first run or updates it and sets the root dir.
     *
     * @param string $root
     * @return template
     */
    public function Template($root = '.')
    {
        global $lang;

        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        // setting pointer "vars"
        $this->vars = &$this->_tpldata['.'][0];
        // load configuration
        $this->tpldir = TEMPLATES_DIR;
        $this->root = $root;
        $this->tpl = basename($root);
        $this->lang =& $lang;
        $this->use_cache = $di->config->get('xs_use_cache');
    }

    /**
     * Destroys this template object. Should be called when you're done with it, in order
     * to clear out the template data so you can load/parse a new template set.
     */
    public function destroy()
    {
        $this->_tpldata = array('.' => array(0 => array()));
        $this->vars = &$this->_tpldata['.'][0];
        $this->xs_started = 0;
    }

    /**
     * Generates a full path+filename for the given filename, which can either
     * be an absolute name, or a name relative to the rootdir for this Template object
     *
     * @param      $filename
     * @param bool $xs_include
     *
     * @return string
     */
    public function make_filename($filename, $xs_include = false)
    {
        // Check replacements list
        if (!$xs_include && isset($this->replace[$filename])) {
            $filename = $this->replace[$filename];
        }
        // Check if it's an absolute or relative path
        if ((substr($filename, 0, 1) !== '/') && (substr($filename, 1, 1) !== ':')) {
            return $this->root . '/' . $filename;
        } else {
            return $filename;
        }
    }

    /**
     * Converts template filename to cache filename
     * Returns empty string if non-cachable (for tpl files outside of root dir)
     * $filename should be absolute filename
     *
     * @param $filename
     *
     * @return string
     */
    public function make_filename_cache($filename)
    {
        $filename = clean_filename(str_replace(TEMPLATES_DIR, '', $filename));
        return $this->cachedir . XS_TPL_PREFIX . $filename . '.' . $this->cached_tpl_ext;
    }

    /**
     * Sets the template filenames for handles. $filename_array
     * Should be a hash of handle => filename pairs
     *
     * @param $filenames
     */
    public function set_filenames($filenames)
    {
        foreach ($filenames as $handle => $filename) {
            $this->set_filename($handle, $filename);
        }
    }

    /**
     * Assigns template filename for handle
     *
     * @param      $handle
     * @param      $filename
     * @param bool $xs_include
     * @param bool $quiet
     *
     * @return bool
     */
    public function set_filename($handle, $filename, $xs_include = false, $quiet = false)
    {
        $can_cache = $this->use_cache;
        $this->files[$handle] = $this->make_filename($filename, $xs_include);
        $this->files_cache[$handle] = '';
        $this->files_cache2[$handle] = '';
        // Checking if we have valid filename
        if (!$this->files[$handle]) {
            if ($xs_include || $quiet) {
                return false;
            } else {
                die("Template->make_filename(): Error - invalid template $filename");
            }
        }
        // Creating cache filename
        if ($can_cache) {
            $this->files_cache2[$handle] = $this->make_filename_cache($this->files[$handle]);
            if (file_exists($this->files_cache2[$handle])) {
                $this->files_cache[$handle] = $this->files_cache2[$handle];
            }
        }
        // Checking if tpl and/or php file exists
        if (empty($this->files_cache[$handle]) && !file_exists($this->files[$handle])) {
            if ($quiet) {
                return false;
            }
            die('Template->make_filename(): template file not found: <br /><br />' . hide_bb_path($this->files[$handle]));
        }
        // Checking if we should recompile cache
        if (!empty($this->files_cache[$handle])) {
            $cache_time = filemtime($this->files_cache[$handle]);
            if (filemtime($this->files[$handle]) > $cache_time) {
                // File was changed, don't use cache file (will be recompled if configuration allowes it)
                $this->files_cache[$handle] = '';
            }
        }
        return true;
    }

    /**
     * Includes file or executes code
     *
     * @param $filename
     * @param $code
     * @param $handle
     */
    public function execute($filename, $code, $handle)
    {
        $this->cur_tpl = $filename;

        global $lang, $user, $tr_cfg;

        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        $L =& $lang;
        $V =& $this->vars;

        if ($filename) {
            include($filename);
        } else {
            eval($code);
        }
    }

    /**
     * Load the file for the handle, compile the file, and run the compiled code
     * This will print out the results of executing the template
     *
     * @param $handle
     *
     * @return bool
     */
    public function pparse($handle)
    {
        // Parsing header if there is one
        if ($this->preparse || $this->postparse) {
            $preparse = $this->preparse;
            $postparse = $this->postparse;
            $this->preparse = '';
            $this->postparse = '';
            if ($preparse) {
                $this->pparse($preparse);
            }
            if ($postparse) {
                $str = $handle;
                $handle = $postparse;
                $this->pparse($str);
            }
        }
        // Checking if handle exists
        if (empty($this->files[$handle]) && empty($this->files_cache[$handle])) {
            die("Template->loadfile(): no files found for handle $handle");
        }
        $this->xs_startup();
        $force_recompile = empty($this->uncompiled_code[$handle]) ? false : true;
        // Checking if php file exists
        if (!empty($this->files_cache[$handle]) && !$force_recompile) {
            // PHP file exists - running it instead of tpl
            $this->execute($this->files_cache[$handle], '', $handle);
            return true;
        }
        if (!$this->loadfile($handle)) {
            die("Template->pparse(): couldn't load template file for handle $handle");
        }
        // Actually compile the template now
        if (empty($this->compiled_code[$handle])) {
            // Actually compile the code now
            if (!empty($this->files_cache2[$handle]) && empty($this->files_cache[$handle]) && !$force_recompile) {
                $this->compiled_code[$handle] = $this->compile2($this->uncompiled_code[$handle], $handle, $this->files_cache2[$handle]);
            } else {
                $this->compiled_code[$handle] = $this->compile2($this->uncompiled_code[$handle], '', '');
            }
        }
        // Run the compiled code
        if (empty($this->files_cache[$handle]) || $force_recompile) {
            $this->execute('', $this->compiled_code[$handle], $handle);
        } else {
            $this->execute($this->files_cache[$handle], '', $handle);
        }
        return true;
    }

    /**
     * Precompile file
     *
     * @param $template
     * @param $filename
     *
     * @return bool
     */
    public function precompile($template, $filename)
    {
        global $precompile_num;
        if (empty($precompile_num)) {
            $precompile_num = 0;
        }
        $precompile_num++;
        $handle = 'precompile_' . $precompile_num;
        // Save old configuration
        $root = $this->root;
        $tpl_name = $this->tpl;
        $old_config = $this->use_cache;
        $old_autosave = $this->auto_compile;
        // Set temporary configuration
        $this->root = $this->tpldir . $template;
        $this->tpl = $template;
        $this->use_cache = 1;
        $this->auto_compile = 1;
        // Set filename
        $res = $this->set_filename($handle, $filename, true, true);
        if (!$res || !$this->files_cache2[$handle]) {
            $this->root = $root;
            $this->tpl = $tpl_name;
            $this->use_cache = $old_config;
            $this->auto_compile = $old_autosave;
            return false;
        }
        $this->files_cache[$handle] = '';
        // Load template
        $res = $this->loadfile($handle);
        if (!$res || empty($this->uncompiled_code[$handle])) {
            $this->root = $root;
            $this->tpl = $tpl_name;
            $this->use_cache = $old_config;
            $this->auto_compile = $old_autosave;
            return false;
        }
        // Compile the code
        $this->compile2($this->uncompiled_code[$handle], $handle, $this->files_cache2[$handle]);
        // Restore configuration
        $this->root = $root;
        $this->tpl = $tpl_name;
        $this->use_cache = $old_config;
        $this->auto_compile = $old_autosave;
        return true;
    }

    /**
     * Inserts the uncompiled code for $handle as the value of $varname in the root-level
     * This can be used to effectively include a template in the middle of another template
     * Note that all desired assignments to the variables in $handle should be done BEFORE calling this function
     *
     * @param $varname
     * @param $handle
     *
     * @return bool
     */
    public function assign_var_from_handle($varname, $handle)
    {
        ob_start();
        $res = $this->pparse($handle);
        $this->vars[$varname] = ob_get_contents();
        ob_end_clean();
        return $res;
    }

    /**
     * Block-level variable assignment. Adds a new block iteration with the given variable assignments
     * Note that this should only be called once per block iteration
     *
     * @param $blockname
     * @param $vararray
     *
     * @return bool
     */
    public function assign_block_vars($blockname, $vararray)
    {
        if (strstr($blockname, '.')) {
            // Nested block
            $blocks = explode('.', $blockname);
            $blockcount = sizeof($blocks) - 1;
            $str = &$this->_tpldata;
            for ($i = 0; $i < $blockcount; $i++) {
                $str = &$str[$blocks[$i] . '.'];
                $str = &$str[sizeof($str) - 1];
            }
            // Now we add the block that we're actually assigning to
            // We're adding a new iteration to this block with the given variable assignments
            $str[$blocks[$blockcount] . '.'][] = $vararray;
        } else {
            // Top-level block. Add a new iteration to this block with the variable assignments we were given
            $this->_tpldata[$blockname . '.'][] = $vararray;
        }
        return true;
    }

    /**
     * Root-level variable assignment. Adds to current assignments, overriding
     * any existing variable assignment with the same name
     *
     * @param $vararray
     */
    public function assign_vars($vararray)
    {
        foreach ($vararray as $key => $val) {
            $this->vars[$key] = $val;
        }
    }

    /**
     * Root-level variable assignment. Adds to current assignments, overriding
     * any existing variable assignment with the same name
     *
     * @param      $varname
     * @param bool $varval
     */
    public function assign_var($varname, $varval = true)
    {
        $this->vars[$varname] = $varval;
    }

    /**
     * Root-level. Adds to current assignments, appends
     * to any existing variable assignment with the same name
     *
     * @param $vararray
     */
    public function append_vars($vararray)
    {
        foreach ($vararray as $key => $val) {
            $this->vars[$key] = !isset($this->vars[$key]) ? $val : $this->vars[$key] . $val;
        }
    }

    /**
     * If not already done, load the file for the given handle and populate
     * the uncompiled_code[] hash with its code. Do not compile
     *
     * @param $handle
     *
     * @return bool
     */
    public function loadfile($handle)
    {
        // If cached file exists do nothing - it will be included via include()
        if (!empty($this->files_cache[$handle])) {
            return true;
        }

        // If the file for this handle is already loaded and compiled, do nothing
        if (!empty($this->uncompiled_code[$handle])) {
            return true;
        }

        // If we don't have a file assigned to this handle, die
        if (empty($this->files[$handle])) {
            die("Template->loadfile(): no file specified for handle $handle");
        }

        $filename = $this->files[$handle];

        if (($str = file_get_contents($filename)) === false) {
            die("Template->loadfile(): file $filename for handle $handle is empty");
        }

        $this->uncompiled_code[$handle] = $str;

        return true;
    }

    /**
     * Generates a reference to the given variable inside the given (possibly nested) block namespace
     * This is a string of the form: $this->_tpldata['parent.'][$_parent_i]['$child1.'][$_child1_i]['$child2.'][$_child2_i]...['varname']
     * It's ready to be inserted into an "echo" line in one of the templates. NOTE: expects a trailing "." on the namespace
     *
     * @param $namespace
     * @param $varname
     *
     * @return string
     */
    public function generate_block_varref($namespace, $varname)
    {
        // Strip the trailing period
        $namespace = substr($namespace, 0, strlen($namespace) - 1);

        // Get a reference to the data block for this namespace
        $varref = $this->generate_block_data_ref($namespace, true);

        // Append the variable reference
        $varref .= "['$varname']";

        $varref = "<?php echo isset($varref) ? $varref : ''; ?>";

        return $varref;
    }

    /**
     * Generates a reference to the array of data values for the given (possibly nested) block namespace
     * This is a string of the form: $this->_tpldata['parent.'][$_parent_i]['$child1.'][$_child1_i]['$child2.'][$_child2_i]...['$childN.']
     * If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above. NOTE: does not expect a trailing "." on the blockname
     *
     * @param $blockname
     * @param $include_last_iterator
     *
     * @return string
     */
    public function generate_block_data_ref($blockname, $include_last_iterator)
    {
        // Get an array of the blocks involved
        $blocks = explode('.', $blockname);
        $blockcount = sizeof($blocks) - 1;
        if ($include_last_iterator) {
            return '$' . $blocks[$blockcount] . '_item';
        } else {
            return '$' . $blocks[$blockcount - 1] . '_item[\'' . $blocks[$blockcount] . '.\']';
        }
    }

    /**
     * @param $filename
     * @param $code
     * @return string
     */
    public function compile_code($filename, $code)
    {
        // Load code from file
        if (!$code && !empty($filename)) {
            $code = file_get_contents($filename);
        }

        // Replace <!-- (END)PHP --> tags
        $search = array('<!-- PHP -->', '<!-- ENDPHP -->');
        $replace = array('<' . '?php ', ' ?' . '>');
        $code = str_replace($search, $replace, $code);

        // Break it up into lines and put " -->" back
        $code_lines = explode(' -->', $code);
        $count = count($code_lines);
        for ($i = 0; $i < ($count - 1); $i++) {
            $code_lines[$i] .= ' -->';
        }

        $block_nesting_level = 0;
        $block_names = array();
        $block_names[0] = ".";
        $block_items = array();
        $count_if = 0;

        // Prepare array for compiled code
        $compiled = array();

        // Array of switches
        $sw = array();

        // Replace all short php tags
        $new_code = array();
        $line_count = count($code_lines);
        for ($i = 0; $i < $line_count; $i++) {
            $line = $code_lines[$i];
            $pos = strpos($line, '<?');
            if ($pos === false) {
                $new_code[] = $line;
                continue;
            }
            if (substr($line, $pos, 5) === '<?php') {
                // valid php tag. skip it
                $new_code[] = substr($line, 0, $pos + 5);
                $code_lines[$i] = substr($line, $pos + 5);
                $i--;
                continue;
            }
            // Invalid php tag
            $new_code[] = substr($line, 0, $pos) . '<?php echo \'<?\'; ?>';
            $code_lines[$i] = substr($line, $pos + 2);
            $i--;
        }
        $code_lines = $new_code;

        // Main loop
        $line_count = count($code_lines);
        for ($i = 0; $i < $line_count; $i++) {
            $line = $code_lines[$i];
            // Reset keyword type
            $keyword_type = XS_TAG_NONE;
            // Check if we have valid keyword in current line
            $pos1 = strpos($line, '<!-- ');
            if ($pos1 === false) {
                // No keywords in this line
                $compiled[] = $this->_compile_text($line);
                continue;
            }
            // Find end of html comment
            $pos2 = strpos($line, ' -->', $pos1);
            if ($pos2 !== false) {
                // Find end of keyword in comment
                $pos3 = strpos($line, ' ', $pos1 + 5);
                if ($pos3 !== false && $pos3 <= $pos2) {
                    $keyword = substr($line, $pos1 + 5, $pos3 - $pos1 - 5);
                    // Check keyword against list of supported keywords. case-sensitive
                    if ($keyword === 'BEGIN') {
                        $keyword_type = XS_TAG_BEGIN;
                    } elseif ($keyword === 'END') {
                        $keyword_type = XS_TAG_END;
                    } elseif ($keyword === 'INCLUDE') {
                        $keyword_type = XS_TAG_INCLUDE;
                    } elseif ($keyword === 'IF') {
                        $keyword_type = XS_TAG_IF;
                    } elseif ($keyword === 'ELSE') {
                        $keyword_type = XS_TAG_ELSE;
                    } elseif ($keyword === 'ELSEIF') {
                        $keyword_type = XS_TAG_ELSEIF;
                    } elseif ($keyword === 'ENDIF') {
                        $keyword_type = XS_TAG_ENDIF;
                    } elseif ($keyword === 'BEGINELSE') {
                        $keyword_type = XS_TAG_BEGINELSE;
                    }
                }
            }
            if (!$keyword_type) {
                // Not valid keyword process the rest of line
                $compiled[] = $this->_compile_text(substr($line, 0, $pos1 + 4));
                $code_lines[$i] = substr($line, $pos1 + 4);
                $i--;
                continue;
            }
            // Remove code before keyword
            if ($pos1 > 0) {
                $compiled[] = $this->_compile_text(substr($line, 0, $pos1));
            }
            // Remove keyword
            $keyword_str = substr($line, $pos1, $pos2 - $pos1 + 4);
            $params_str = $pos2 == $pos3 ? '' : substr($line, $pos3 + 1, $pos2 - $pos3 - 1);
            $code_lines[$i] = substr($line, $pos2 + 4);
            $i--;

            /*
            * <!-- BEGIN -->
            */
            if ($keyword_type == XS_TAG_BEGIN) {
                $params = explode(' ', $params_str);
                $num_params = count($params);
                // Get variable name
                if ($num_params == 1) {
                    $var = $params[0];
                } elseif ($num_params == 2) {
                    if ($params[0] === '') {
                        $var = $params[1];
                    } elseif ($params[1] === '') {
                        $var = $params[0];
                    } else {
                        // Invalid tag
                        $compiled[] = $keyword_str;
                        continue;
                    }
                } else {
                    // Invalid tag
                    $compiled[] = $keyword_str;
                    continue;
                }
                // Adding code
                $block_nesting_level++;
                $block_names[$block_nesting_level] = $var;
                if (isset($block_items[$var])) {
                    $block_items[$var]++;
                } else {
                    $block_items[$var] = 1;
                }
                if ($block_nesting_level < 2) {
                    // Block is not nested
                    $line = '<' . "?php\n\n";
                    $line .= '$' . $var . '_count = ( isset($this->_tpldata[\'' . $var . '.\']) ) ?  sizeof($this->_tpldata[\'' . $var . '.\']) : 0;';
                    $line .= "\n" . 'for ($' . $var . '_i = 0; $' . $var . '_i < $' . $var . '_count; $' . $var . '_i++)';
                    $line .= "\n" . '{' . "\n";
                    $line .= ' $' . $var . '_item = &$this->_tpldata[\'' . $var . '.\'][$' . $var . '_i];' . "\n";
                    $line .= " \${$var}_item['S_ROW_COUNT'] = \${$var}_i;\n";
                    $line .= " \${$var}_item['S_NUM_ROWS'] = \${$var}_count;\n";
                    $line .= "\n?" . ">";
                } else {
                    // This block is nested. Generate a namespace string for this block
                    $namespace = join('.', $block_names);
                    // Strip leading period from root level
                    $namespace = substr($namespace, 2);
                    // Get a reference to the data array for this block that depends on the current indices of all parent blocks
                    $varref = $this->generate_block_data_ref($namespace, false);
                    // Create the for loop code to iterate over this block
                    $line = '<' . "?php\n\n";
                    $line .= '$' . $var . '_count = ( isset(' . $varref . ') ) ? sizeof(' . $varref . ') : 0;';
                    $line .= "\n" . 'for ($' . $var . '_i = 0; $' . $var . '_i < $' . $var . '_count; $' . $var . '_i++)';
                    $line .= "\n" . '{' . "\n";
                    $line .= ' $' . $var . '_item = &' . $varref . '[$' . $var . '_i];' . "\n";
                    $line .= " \${$var}_item['S_ROW_COUNT'] = \${$var}_i;\n";
                    $line .= " \${$var}_item['S_NUM_ROWS'] = \${$var}_count;\n";
                    $line .= "\n?" . ">";
                }
                $compiled[] = $line;
                continue;
            }

            /*
            * <!-- END -->
            */
            if ($keyword_type == XS_TAG_END) {
                $params = explode(' ', $params_str);
                $num_params = count($params);
                if ($num_params == 1) {
                    $var = $params[0];
                } elseif ($num_params == 2 && $params[0] === '') {
                    $var = $params[1];
                } elseif ($num_params == 2 && $params[1] === '') {
                    $var = $params[0];
                } else {
                    $compiled[] = $keyword_str;
                    continue;
                }
                // We have the end of a block
                $line = '<' . "?php\n\n";
                $line .= '} // END ' . $var . "\n\n";
                $line .= 'if(isset($' . $var . '_item)) { unset($' . $var . '_item); } ';
                $line .= "\n\n?" . ">";
                if (isset($block_items[$var])) {
                    $block_items[$var]--;
                } else {
                    $block_items[$var] = -1;
                }
                unset($block_names[$block_nesting_level]);
                $block_nesting_level--;
                $compiled[] = $line;
                continue;
            }

            /*
            * <!-- BEGINELSE -->
            */
            if ($keyword_type == XS_TAG_BEGINELSE) {
                if ($block_nesting_level) {
                    $var = $block_names[$block_nesting_level];
                    $compiled[] = '<' . '?php } if(!$' . $var . '_count) { ?' . '>';
                } else {
                    $compiled[] = $keyword_str;
                    continue;
                }
            }

            /*
            * <!-- INCLUDE -->
            */
            if ($keyword_type == XS_TAG_INCLUDE) {
                $params = explode(' ', $params_str);
                $num_params = count($params);
                if ($num_params != 1) {
                    $compiled[] = $keyword_str;
                    continue;
                }
                $line = '<' . '?php ';
                $filehash = md5($params_str . $this->include_count . TIMENOW);
                $line .= ' $this->set_filename(\'xs_include_' . $filehash . '\', \'' . $params_str . '\', true); ';
                $line .= ' $this->pparse(\'xs_include_' . $filehash . '\'); ';
                $line .= ' ?' . '>';
                $this->include_count++;
                $compiled[] = $line;
                continue;
            }

            /*
            * <!-- IF -->
            */
            if ($keyword_type == XS_TAG_IF || $keyword_type == XS_TAG_ELSEIF) {
                if (!$count_if) {
                    $keyword_type = XS_TAG_IF;
                }
                $str = $this->compile_tag_if($params_str, $keyword_type == XS_TAG_IF ? false : true);
                if ($str) {
                    $compiled[] = '<?php ' . $str . ' ?>';
                    if ($keyword_type == XS_TAG_IF) {
                        $count_if++;
                    }
                } else {
                    $compiled[] = $keyword_str;
                }
                continue;
            }

            /*
            * <!-- ELSE -->
            */
            if ($keyword_type == XS_TAG_ELSE && $count_if > 0) {
                $compiled[] = '<?php } else { ?>';
                continue;
            }

            /*
            * <!-- ENDIF -->
            */
            if ($keyword_type == XS_TAG_ENDIF && $count_if > 0) {
                $compiled[] = '<?php } ?>';
                $count_if--;
                continue;
            }
        }

        // Bring it back into a single string
        $code_header = '';
        $code_footer = '';

        return $code_header . join('', $compiled) . $code_footer;
    }

    /*
    * Compile code between tags
    */
    /**
     * @param $code
     * @return mixed
     */
    public function _compile_text($code)
    {
        if (strlen($code) < 3) {
            return $code;
        }
        // Change template varrefs into PHP varrefs. This one will handle varrefs WITH namespaces
        $varrefs = array();
        preg_match_all('#\{(([a-z0-9\-_]+?\.)+)([a-z0-9\-_]+?)\}#is', $code, $varrefs);
        $varcount = sizeof($varrefs[1]);
        $search = array();
        $replace = array();
        for ($i = 0; $i < $varcount; $i++) {
            $namespace = $varrefs[1][$i];
            $varname = $varrefs[3][$i];
            $new = $this->generate_block_varref($namespace, $varname);
            $search[] = $varrefs[0][$i];
            $replace[] = $new;
        }
        if (count($search) > 0) {
            $code = str_replace($search, $replace, $code);
        }
        // This will handle the remaining root-level varrefs
        $code = preg_replace('#\{(L_([a-z0-9\-_]+?))\}#i', '<?php echo isset($L[\'$2\']) ? $L[\'$2\'] : $V[\'$1\']; ?>', $code);
        $code = preg_replace('#\{(\$[a-z_][a-z0-9_$\->\'\"\.\[\]]*?)\}#i', '<?php echo isset($1) ? $1 : \'\'; ?>', $code);
        $code = preg_replace('#\{(\#([a-z_][a-z0-9_]*?))\}#i', '<?php echo defined(\'$2\') ? $2 : \'\'; ?>', $code);
        $code = preg_replace('#\{([a-z0-9\-_]+?)\}#i', '<?php echo isset($V[\'$1\']) ? $V[\'$1\'] : \'\'; ?>', $code);
        return $code;
    }

    //
    // Compile IF tags - much of this is from Smarty with some adaptions for our block level methods
    //
    /**
     * @param $tag_args
     * @param $elseif
     * @return string
     */
    public function compile_tag_if($tag_args, $elseif)
    {
        /* Tokenize args for 'if' tag */
        preg_match_all('/(?:
						 "[^"\\\\]*(?:\\\\.[^"\\\\]*)*"     |
						 \'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\' |
						 [(),]                              |
						 [^\s(),]+)/x', $tag_args, $match);

        $tokens = $match[0];
        $tokens_cnt = count($tokens);
        $is_arg_stack = array();

        for ($i = 0; $i < $tokens_cnt; $i++) {
            $token = &$tokens[$i];

            switch ($token) {
                case 'eq':
                    $token = '==';
                    break;

                case 'ne':
                case 'neq':
                    $token = '!=';
                    break;

                case 'lt':
                    $token = '<';
                    break;

                case 'le':
                case 'lte':
                    $token = '<=';
                    break;

                case 'gt':
                    $token = '>';
                    break;

                case 'ge':
                case 'gte':
                    $token = '>=';
                    break;

                case 'and':
                    $token = '&&';
                    break;

                case 'or':
                    $token = '||';
                    break;

                case 'not':
                    $token = '!';
                    break;

                case 'mod':
                    $token = '%';
                    break;

                case '(':
                    array_push($is_arg_stack, $i);
                    break;

                case 'is':
                    $is_arg_start = ($tokens[$i - 1] == ')') ? array_pop($is_arg_stack) : $i - 1;
                    $is_arg = join('	', array_slice($tokens, $is_arg_start, $i - $is_arg_start));

                    $new_tokens = $this->_parse_is_expr($is_arg, array_slice($tokens, $i + 1));

                    array_splice($tokens, $is_arg_start, count($tokens), $new_tokens);

                    $i = $is_arg_start;
                    break;

                default:
                    $pattern = '@^
					  (                                      #  1
					    ([a-z0-9\-_]+?\.)+?                  #  2  block tpl vars (VAR1.VAR2.) but without last
					  )?
					  (                                      #  3
					    ([a-z_][a-z0-9\-_]*)?                #  4  single tpl var or last block var (last in block)
					    (\$[a-z_][a-z0-9_$\->\'\"\.\[\]]*)?  #  5  php var
					    (\#([a-z_][a-z0-9_]*))?              #  7  php const
					  )
					$@ix';
                    if (preg_match($pattern, $token, $m)) {
                        if (!empty($m[1])) {
                            $token = $this->generate_block_data_ref(substr($m[1], 0, -1), true) . "['{$m[4]}']";
                        } elseif (!empty($m[4])) {
                            $token = ($tokens_cnt == 1) ? "!empty(\$V['{$m[4]}'])" : "\$V['{$m[4]}']";
                        } elseif (!empty($m[5])) {
                            $token = ($tokens_cnt == 1) ? "!empty({$m[5]})" : "{$m[5]}";
                        } elseif (!empty($m[7])) {
                            $token = ($tokens_cnt == 1) ? "defined('{$m[7]}') && {$m[7]}" : "{$m[7]}";
                        }
                    }
                    break;
            }
        }

        if ($elseif) {
            $code = '} elseif (' . join(' ', $tokens) . ') {';
        } else {
            $code = 'if (' . join(' ', $tokens) . ') {';
        }

        return $code;
    }

    // This is from Smarty
    /**
     * @param $is_arg
     * @param $tokens
     * @return mixed
     */
    public function _parse_is_expr($is_arg, $tokens)
    {
        $expr_end = 0;
        $negate_expr = false;

        if (($first_token = array_shift($tokens)) == 'not') {
            $negate_expr = true;
            $expr_type = array_shift($tokens);
        } else {
            $expr_type = $first_token;
        }

        switch ($expr_type) {
            case 'even':
                if ($tokens[$expr_end] == 'by') {
                    $expr_end++;
                    $expr_arg = $tokens[$expr_end++];
                    $expr = "!(($is_arg	/ $expr_arg) % $expr_arg)";
                } else {
                    $expr = "!($is_arg % 2)";
                }
                break;

            case 'odd':
                if ($tokens[$expr_end] == 'by') {
                    $expr_end++;
                    $expr_arg = $tokens[$expr_end++];
                    $expr = "(($is_arg / $expr_arg)	% $expr_arg)";
                } else {
                    $expr = "($is_arg %	2)";
                }
                break;

            case 'div':
                if ($tokens[$expr_end] == 'by') {
                    $expr_end++;
                    $expr_arg = $tokens[$expr_end++];
                    $expr = "!($is_arg % $expr_arg)";
                }
                break;

            default:
                break;
        }

        if ($negate_expr) {
            $expr = "!($expr)";
        }

        array_splice($tokens, 0, $expr_end, $expr);

        return $tokens;
    }

    /**
     * Compiles code and writes to cache if needed
     *
     * @param $code
     * @param $handle
     * @param $cache_file
     *
     * @return string
     */
    public function compile2($code, $handle, $cache_file)
    {
        $code = $this->compile_code('', $code);
        if ($cache_file && !empty($this->use_cache) && !empty($this->auto_compile)) {
            $res = $this->write_cache($cache_file, $code);
            if ($handle && $res) {
                $this->files_cache[$handle] = $cache_file;
            }
        }
        $code = '?' . '>' . $code . '<' . "?php\n";
        return $code;
    }

    /**
     * Compiles the given string of code, and returns the result in a string.
     * If "do_not_echo" is true, the returned code will not be directly executable,
     * but can be used as part of a variable assignment for use in assign_code_from_handle().
     * This function isn't used and kept only for compatibility with original template.php
     *
     * @param        $code
     * @param bool $do_not_echo
     * @param string $retvar
     *
     * @return string
     */
    public function compile($code, $do_not_echo = false, $retvar = '')
    {
        $code = ' ?' . '>' . $this->compile_code('', $code) . '<' . "?php \n";
        if ($do_not_echo) {
            $code = "ob_start();\n" . $code . "\n\${$retvar} = ob_get_contents();\nob_end_clean();\n";
        }
        return $code;
    }

    /**
     * Write cache to disk
     *
     * @param $filename
     * @param $code
     */
    public function write_cache($filename, $code)
    {
        file_write($code, $filename, false, true, true);
    }

    /**
     * Запуск шаблонизатора
     */
    public function xs_startup()
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        if (empty($this->xs_started)) {
            $this->xs_started = 1;
            // Adding language variable
            $this->vars['LANG'] = isset($this->vars['LANG']) ? $this->vars['LANG'] : $di->config->get('default_lang');
            // Adding current template
            $tpl = $this->root . '/';
            if (substr($tpl, 0, 2) === './') {
                $tpl = substr($tpl, 2, strlen($tpl));
            }
            $this->vars['TEMPLATE'] = isset($this->vars['TEMPLATE']) ? $this->vars['TEMPLATE'] : $tpl;
            $this->vars['TEMPLATE_NAME'] = isset($this->vars['TEMPLATE_NAME']) ? $this->vars['TEMPLATE_NAME'] : $this->tpl;
        }
    }

    /**
     * @param $var
     * @return string
     */
    public function lang_error($var)
    {
        trigger_error(basename($this->cur_tpl) . " : undefined language variable {L_{$var}}", E_USER_WARNING);
        return "Undefined: {L_{$var}}";
    }
}
