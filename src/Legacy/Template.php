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

namespace TorrentPier\Legacy;

/**
 * Class Template
 * @package TorrentPier\Legacy
 */
class Template
{
    /**
     * Variable that holds all the data we'll be substituting into the compiled templates.
     * This will end up being a multi-dimensional array like this:
     * $this->_tpldata[block.][iteration#][child.][iteration#][child2.][iteration#][variablename] == value
     * if it's a root-level variable, it'll be like this:
     * $this->vars[varname] == value  or  $this->_tpldata['.'][0][varname] == value
     *
     * @var array
     */
    public $_tpldata = [
        '.' => [
            0 => []
        ]
    ];

    /** @var array added for easier access to data */
    public $vars;

    /** @var array hash of filenames for each template handle */
    public $files = [];

    /** @var array cache files that exists */
    public $files_cache = [];

    /** @var array cache files (exists or not exists) */
    public $files_cache2 = [];

    /** @var string root template directory */
    public $root = '';

    /** @var string cache directory */
    public $cachedir = CACHE_DIR . '/';

    /** @var string template root directory */
    public $tpldir = '';

    /** @var string default template directory */
    public $tpldef = 'default';

    /** @var array this will hash handle names to the compiled code for that handle */
    public $compiled_code = [];

    /** @var array this will hold the uncompiled code for that handle */
    public $uncompiled_code = [];

    /** @var int cache settings */
    public $use_cache = 1;
    public $cache_writable = 1;

    /** @var int auto-compile setting */
    public $auto_compile = 1;

    /** @var string current template name */
    public $tpl = '';
    public $cur_tpl = '';

    /** @var array list of replacements */
    public $replace = [];

    /** @var int counter for include */
    public $include_count = 0;

    /** @var string extension tpl-cache files */
    public $cached_tpl_ext = 'php';

    /** @var string these handles will be parsed if pparse() is executed */
    public $preparse = '';
    public $postparse = '';

    /** @var bool subtemplates mod detection */
    public $subtemplates = false;

    /** @var array style configuration */
    public $style_config = [];

    public $lang = [];

    /**
     * Constructor. Installs XS mod on first run or updates it and sets the root dir.
     *
     * @param string $root
     */
    public function __construct($root = '.')
    {
        global $bb_cfg, $lang;

        // setting pointer "vars"
        $this->vars = &$this->_tpldata['.'][0];
        // load configuration
        $this->tpldir = TEMPLATES_DIR;
        $this->root = $root;
        $this->tpl = basename($root);
        $this->lang =& $lang;
        $this->use_cache = $bb_cfg['xs_use_cache'];
    }

    /**
     * Generates a full path+filename for the given filename, which can either
     * be an absolute name, or a name relative to the rootdir for this Template object.
     *
     * @param $filename
     * @param bool $xs_include
     *
     * @return mixed|string
     */
    public function make_filename($filename, $xs_include = false)
    {
        // Check replacements list
        if (!$xs_include && isset($this->replace[$filename])) {
            $filename = $this->replace[$filename];
        }
        // Check if it's an absolute or relative path.
        if (($filename[0] !== '/') && ($filename[1] !== ':')) {
            return $this->root . '/' . $filename;
        }

        return $filename;
    }

    /**
     * Converts template filename to cache filename.
     * Returns empty string if non-cachable (for tpl files outside of root dir).
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
     * Sets the template filenames for handles.
     * $filename_array should be a hash of handle => filename pairs.
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
     * Assigns template filename for handle.
     *
     * @param $handle
     * @param $filename
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
        // checking if we have valid filename
        if (!$this->files[$handle]) {
            if ($xs_include || $quiet) {
                return false;
            }

            die("Template->make_filename(): Error - invalid template $filename");
        }
        // creating cache filename
        if ($can_cache) {
            $this->files_cache2[$handle] = $this->make_filename_cache($this->files[$handle]);
            if (@file_exists($this->files_cache2[$handle])) {
                $this->files_cache[$handle] = $this->files_cache2[$handle];
            }
        }
        // checking if tpl and/or php file exists
        if (empty($this->files_cache[$handle]) && !@file_exists($this->files[$handle])) {
            if ($quiet) {
                return false;
            }
            die('Template->make_filename(): Error - template file not found: <br /><br />' . hide_bb_path($this->files[$handle]));
        }
        // checking if we should recompile cache
        if (!empty($this->files_cache[$handle])) {
            $cache_time = @filemtime($this->files_cache[$handle]);
            if (@filemtime($this->files[$handle]) > $cache_time) {
                // file was changed. don't use cache file (will be recompled if configuration allowes it)
                $this->files_cache[$handle] = '';
            }
        }
        return true;
    }

    /**
     * includes file or executes code
     *
     * @param $filename
     * @param $code
     * @param $handle
     */
    public function execute($filename, $code, $handle)
    {
        $this->cur_tpl = $filename;

        global $lang, $bb_cfg, $user;

        $L =& $lang;
        $V =& $this->vars;

        if ($filename) {
            include $filename;
        } else {
            eval($code);
        }
    }

    /**
     * Load the file for the handle, compile the file and run the compiled code.
     * This will print out the results of executing the template.
     *
     * @param $handle
     *
     * @return bool
     */
    public function pparse($handle)
    {
        // parsing header if there is one
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
        // checking if handle exists
        if (empty($this->files[$handle]) && empty($this->files_cache[$handle])) {
            die("Template->loadfile(): No files found for handle $handle");
        }
        $this->xs_startup();
        $force_recompile = empty($this->uncompiled_code[$handle]) ? false : true;
        // checking if php file exists.
        if (!empty($this->files_cache[$handle]) && !$force_recompile) {
            // php file exists - running it instead of tpl
            $this->execute($this->files_cache[$handle], '', $handle);
            return true;
        }
        if (!$this->loadfile($handle)) {
            die("Template->pparse(): Could not load template file for handle $handle");
        }
        // actually compile the template now.
        if (empty($this->compiled_code[$handle])) {
            // Actually compile the code now.
            if (!empty($this->files_cache2[$handle]) && empty($this->files_cache[$handle]) && !$force_recompile) {
                $this->compiled_code[$handle] = $this->compile2($this->uncompiled_code[$handle], $handle, $this->files_cache2[$handle]);
            } else {
                $this->compiled_code[$handle] = $this->compile2($this->uncompiled_code[$handle], '', '');
            }
        }
        // Run the compiled code.
        if (empty($this->files_cache[$handle]) || $force_recompile) {
            $this->execute('', $this->compiled_code[$handle], $handle);
        } else {
            $this->execute($this->files_cache[$handle], '', $handle);
        }
        return true;
    }

    /**
     * Block-level variable assignment. Adds a new block iteration with the given
     * variable assignments. Note that this should only be called once per block iteration.
     *
     * @param $blockname
     * @param $vararray
     *
     * @return bool
     */
    public function assign_block_vars($blockname, $vararray)
    {
        if (false !== strpos($blockname, '.')) {
            // Nested block.
            $blocks = explode('.', $blockname);
            $blockcount = count($blocks) - 1;

            $str = &$this->_tpldata;
            for ($i = 0; $i < $blockcount; $i++) {
                $str = &$str[$blocks[$i] . '.'];
                $str = &$str[count($str) - 1];
            }
            // Now we add the block that we're actually assigning to.
            // We're adding a new iteration to this block with the given
            //	variable assignments.
            $str[$blocks[$blockcount] . '.'][] = $vararray;
        } else {
            // Top-level block.
            // Add a new iteration to this block with the variable assignments
            // we were given.
            $this->_tpldata[$blockname . '.'][] = $vararray;
        }

        return true;
    }

    /**
     * Root-level variable assignment. Adds to current assignments, overriding
     * any existing variable assignment with the same name.
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
     * any existing variable assignment with the same name.
     *
     * @param $varname
     * @param bool $varval
     */
    public function assign_var($varname, $varval = true)
    {
        $this->vars[$varname] = $varval;
    }

    /**
     * If not already done, load the file for the given handle and populate
     * the uncompiled_code[] hash with its code. Do not compile.
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

        // If the file for this handle is already loaded and compiled, do nothing.
        if (!empty($this->uncompiled_code[$handle])) {
            return true;
        }

        // If we don't have a file assigned to this handle, die.
        if (empty($this->files[$handle])) {
            die("Template->loadfile(): No file specified for handle $handle");
        }

        $filename = $this->files[$handle];

        if (($str = @file_get_contents($filename)) === false) {
            die("Template->loadfile(): File $filename for handle $handle is empty");
        }

        $this->uncompiled_code[$handle] = $str;

        return true;
    }

    /**
     * Generates a reference to the given variable inside the given (possibly nested)
     * block namespace. This is a string of the form:
     * ' . $this->_tpldata['parent.'][$_parent_i]['$child1.'][$_child1_i]['$child2.'][$_child2_i]...['varname'] . '
     * It's ready to be inserted into an "echo" line in one of the templates.
     * NOTE: expects a trailing "." on the namespace.
     *
     * @param $namespace
     * @param $varname
     *
     * @return string
     */
    public function generate_block_varref($namespace, $varname)
    {
        // Strip the trailing period.
        $namespace = substr($namespace, 0, strlen($namespace) - 1);

        // Get a reference to the data block for this namespace.
        $varref = $this->generate_block_data_ref($namespace, true);
        // Prepend the necessary code to stick this in an echo line.

        // Append the variable reference.
        $varref .= "['$varname']";

        $varref = "<?php echo isset($varref) ? $varref : ''; ?>";

        return $varref;
    }

    /**
     * Generates a reference to the array of data values for the given
     * (possibly nested) block namespace. This is a string of the form:
     * $this->_tpldata['parent.'][$_parent_i]['$child1.'][$_child1_i]['$child2.'][$_child2_i]...['$childN.']
     *
     * If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
     * NOTE: does not expect a trailing "." on the blockname.
     *
     * @param $blockname
     * @param $include_last_iterator
     *
     * @return string
     */
    public function generate_block_data_ref($blockname, $include_last_iterator)
    {
        // Get an array of the blocks involved.
        $blocks = explode('.', $blockname);
        $blockcount = count($blocks) - 1;
        if ($include_last_iterator) {
            return '$' . $blocks[$blockcount] . '_item';
        }

        return '$' . $blocks[$blockcount - 1] . '_item[\'' . $blocks[$blockcount] . '.\']';
    }

    public function compile_code($filename, $code)
    {
        //	$filename - file to load code from. used if $code is empty
        //	$code - tpl code

        // load code from file
        if (!$code && !empty($filename)) {
            $code = file_get_contents($filename);
        }

        // Replace <!-- (END)PHP --> tags
        $search = array('<!-- PHP -->', '<!-- ENDPHP -->');
        $replace = array('<' . '?php ', ' ?' . '>');
        $code = str_replace($search, $replace, $code);

        // Break it up into lines and put " -->" back.
        $code_lines = explode(' -->', $code);
        $count = count($code_lines);
        for ($i = 0; $i < ($count - 1); $i++) {
            $code_lines[$i] .= ' -->';
        }

        $block_nesting_level = 0;
        $block_names = array();
        $block_names[0] = '.';
        $block_items = array();
        $count_if = 0;

        // prepare array for compiled code
        $compiled = array();

        // array of switches
        $sw = array();

        // replace all short php tags
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
            // invalid php tag
            $new_code[] = substr($line, 0, $pos) . '<?php echo \'<?\'; ?>';
            $code_lines[$i] = substr($line, $pos + 2);
            $i--;
        }
        $code_lines = $new_code;

        // main loop
        $line_count = count($code_lines);
        for ($i = 0; $i < $line_count; $i++) {
            $line = $code_lines[$i];
            // reset keyword type
            $keyword_type = XS_TAG_NONE;
            // check if we have valid keyword in current line
            $pos1 = strpos($line, '<!-- ');
            if ($pos1 === false) {
                // no keywords in this line
                $compiled[] = $this->_compile_text($line);
                continue;
            }
            // find end of html comment
            $pos2 = strpos($line, ' -->', $pos1);
            if ($pos2 !== false) {
                // find end of keyword in comment
                $pos3 = strpos($line, ' ', $pos1 + 5);
                if ($pos3 !== false && $pos3 <= $pos2) {
                    $keyword = substr($line, $pos1 + 5, $pos3 - $pos1 - 5);
                    // check keyword against list of supported keywords. case-sensitive
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
                // not valid keyword. process the rest of line
                $compiled[] = $this->_compile_text(substr($line, 0, $pos1 + 4));
                $code_lines[$i] = substr($line, $pos1 + 4);
                $i--;
                continue;
            }
            // remove code before keyword
            if ($pos1 > 0) {
                $compiled[] = $this->_compile_text(substr($line, 0, $pos1));
            }
            // remove keyword
            $keyword_str = substr($line, $pos1, $pos2 - $pos1 + 4);
            $params_str = $pos2 == $pos3 ? '' : substr($line, $pos3 + 1, $pos2 - $pos3 - 1);
            $code_lines[$i] = substr($line, $pos2 + 4);
            $i--;
            // Check keywords

            /*
            * <!-- BEGIN -->
            */
            if ($keyword_type == XS_TAG_BEGIN) {
                $params = explode(' ', $params_str);
                $num_params = count($params);
                // get variable name
                if ($num_params == 1) {
                    $var = $params[0];
                } elseif ($num_params == 2) {
                    if ($params[0] === '') {
                        $var = $params[1];
                    } elseif ($params[1] === '') {
                        $var = $params[0];
                    } else {
                        // invalid tag
                        $compiled[] = $keyword_str;
                        continue;
                    }
                } else {
                    // invalid tag
                    $compiled[] = $keyword_str;
                    continue;
                }
                // adding code
                $block_nesting_level++;
                $block_names[$block_nesting_level] = $var;
                if (isset($block_items[$var])) {
                    $block_items[$var]++;
                } else {
                    $block_items[$var] = 1;
                }
                if ($block_nesting_level < 2) {
                    // Block is not nested.
                    $line = '<' . "?php\n\n";
                    $line .= '$' . $var . '_count = ( isset($this->_tpldata[\'' . $var . '.\']) ) ?  sizeof($this->_tpldata[\'' . $var . '.\']) : 0;';
                    $line .= "\n" . 'for ($' . $var . '_i = 0; $' . $var . '_i < $' . $var . '_count; $' . $var . '_i++)';
                    $line .= "\n" . '{' . "\n";
                    $line .= ' $' . $var . '_item = &$this->_tpldata[\'' . $var . '.\'][$' . $var . '_i];' . "\n";
                    $line .= " \${$var}_item['S_ROW_COUNT'] = \${$var}_i;\n";
                    $line .= " \${$var}_item['S_NUM_ROWS'] = \${$var}_count;\n";
                    $line .= "\n?" . '>';
                } else {
                    // This block is nested.
                    // Generate a namespace string for this block.
                    $namespace = implode('.', $block_names);
                    // strip leading period from root level..
                    $namespace = substr($namespace, 2);
                    // Get a reference to the data array for this block that depends on the
                    // current indices of all parent blocks.
                    $varref = $this->generate_block_data_ref($namespace, false);
                    // Create the for loop code to iterate over this block.
                    $line = '<' . "?php\n\n";
                    $line .= '$' . $var . '_count = ( isset(' . $varref . ') ) ? sizeof(' . $varref . ') : 0;';
                    $line .= "\n" . 'for ($' . $var . '_i = 0; $' . $var . '_i < $' . $var . '_count; $' . $var . '_i++)';
                    $line .= "\n" . '{' . "\n";
                    $line .= ' $' . $var . '_item = &' . $varref . '[$' . $var . '_i];' . "\n";
                    $line .= " \${$var}_item['S_ROW_COUNT'] = \${$var}_i;\n";
                    $line .= " \${$var}_item['S_NUM_ROWS'] = \${$var}_count;\n";
                    $line .= "\n?" . '>';
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
                // We have the end of a block.
                $line = '<' . "?php\n\n";
                $line .= '} // END ' . $var . "\n\n";
                $line .= 'if(isset($' . $var . '_item)) { unset($' . $var . '_item); } ';
                $line .= "\n\n?" . '>';
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
                $str = $this->compile_tag_if($params_str, $keyword_type != XS_TAG_IF);
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

        // bring it back into a single string.
        $code_header = '';
        $code_footer = '';

        return $code_header . implode('', $compiled) . $code_footer;
    }

    /**
     * Compile code between tags
     *
     * @param $code
     *
     * @return mixed
     */
    public function _compile_text($code)
    {
        if (strlen($code) < 3) {
            return $code;
        }
        // change template varrefs into PHP varrefs
        // This one will handle varrefs WITH namespaces
        $varrefs = array();
        preg_match_all('#\{(([a-z0-9\-_]+?\.)+)([a-z0-9\-_]+?)\}#is', $code, $varrefs);
        $varcount = count($varrefs[1]);
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

    /**
     * Compile IF tags - much of this is from Smarty with some adaptions for our block level methods
     *
     * @param $tag_args
     * @param $elseif
     * @return string
     */
    public function compile_tag_if($tag_args, $elseif)
    {
        /* Tokenize args for 'if' tag. */
        preg_match_all('/(?:
										 "[^"\\\\]*(?:\\\\.[^"\\\\]*)*"         |
										 \'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'     |
										 [(),]                                  |
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
                    $is_arg_stack[] = $i;
                    break;

                case 'is':
                    $is_arg_start = ($tokens[$i - 1] == ')') ? array_pop($is_arg_stack) : $i - 1;
                    $is_arg = implode('	', array_slice($tokens, $is_arg_start, $i - $is_arg_start));

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
            $code = '} elseif (' . implode(' ', $tokens) . ') {';
        } else {
            $code = 'if (' . implode(' ', $tokens) . ') {';
        }

        return $code;
    }

    /**
     * This is from Smarty
     *
     * @param $is_arg
     * @param $tokens
     *
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
                if (@$tokens[$expr_end] == 'by') {
                    $expr_end++;
                    $expr_arg = $tokens[$expr_end++];
                    $expr = "!(($is_arg	/ $expr_arg) % $expr_arg)";
                } else {
                    $expr = "!($is_arg % 2)";
                }
                break;

            case 'odd':
                if (@$tokens[$expr_end] == 'by') {
                    $expr_end++;
                    $expr_arg = $tokens[$expr_end++];
                    $expr = "(($is_arg / $expr_arg)	% $expr_arg)";
                } else {
                    $expr = "($is_arg %	2)";
                }
                break;

            case 'div':
                if (@$tokens[$expr_end] == 'by') {
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
     * Write cache to disk
     *
     * @param $filename
     * @param $code
     */
    public function write_cache($filename, $code)
    {
        file_write($code, $filename, false, true, true);
    }

    public function xs_startup()
    {
        global $bb_cfg;

        // adding language variable (eg: "english" or "german")
        // can be used to make truly multi-lingual templates
        $this->vars['LANG'] = isset($this->vars['LANG']) ? $this->vars['LANG'] : $bb_cfg['default_lang'];
        // adding current template
        $tpl = $this->root . '/';
        if (substr($tpl, 0, 2) === './') {
            $tpl = substr($tpl, 2, strlen($tpl));
        }
        $this->vars['TEMPLATE'] = isset($this->vars['TEMPLATE']) ? $this->vars['TEMPLATE'] : $tpl;
        $this->vars['TEMPLATE_NAME'] = isset($this->vars['TEMPLATE_NAME']) ? $this->vars['TEMPLATE_NAME'] : $this->tpl;
    }
}
