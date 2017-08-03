<?php
/*
xLanguageParser - Parsing the content and extract out the corrent language content

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General 
Public License as published by the Free Software Foundation, either version 2 of the License, or (at your 
option) any later version.

This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage. See the GNU General Public License for
more details.

For full license details see license.txt
============================================================================================================ */

/**
 * xLanguageXhtmlParser class
 * This extract the parts using the <... lang="..."> tag
 *
 * @package xLanguage
 * @author Sam Wong
 * @copyright Copyright (C) 2008 Sam Wong
 **/
class xLanguageXHtmlParser {
    function xLanguageXHtmlParser() {
        $this->mode = 5;
    }
    
    /**
     * Start tag handler for XML parser
     *
     * @return void
     **/
    function start_tag($parser, $name, $attr) {
        if (xml_get_current_byte_index($parser) == 0) $this->mode = 4;
        
        $last = strrpos(substr($this->orig, 0, xml_get_current_byte_index($parser) + 1), '<');
        if ($this->curlang) {
            $this->content .= $this->precontent . substr($this->orig, $this->startmark, $last - $this->startmark);
        }
        if (isset($attr['LANG'])) {
            if (in_array($attr['LANG'], $this->lang)) {
                $last = strpos($this->orig, '>', $last) + 1;
                $this->level[] = true;
                $this->curlang = true;

                $this->precontent = '<' . strtolower(htmlspecialchars($name));
                foreach ($attr as $k => $v) {
                    if ($k == 'LANG') $v = $this->options['language'][$v]['shownas'];
                    $this->precontent .= ' ' . strtolower(htmlspecialchars($k)) . '="' . htmlspecialchars($v) . '"';
                }
                $this->precontent .= '>';
            } else {
                $this->level[] = false;
                $this->curlang = false;
                $this->precontent = '';
            }
        } else {
            $this->level[] = $this->curlang;
            $this->precontent = '';
        }
        $this->startmark = $last;
    }

    /**
     * End tag handler for XML parser
     *
     * @return void
     **/
    function end_tag($parser, $name) {
        $this->curlang = array_pop($this->level);

        if ($this->mode == 4) {
            if (substr($this->orig, xml_get_current_byte_index($parser) - 2, 1) == '/') {
                // <aaa />
                $last = strpos($this->orig, '>', xml_get_current_byte_index($parser) - 1) + 1;
            } else {
                // Case: <aaa></aaa>
                $last = strpos($this->orig, '>', xml_get_current_byte_index($parser)) + 1;
            }
        } else {
            $last = xml_get_current_byte_index($parser);
        }
        if ($this->curlang) {
            $this->content .= $this->precontent . substr($this->orig, $this->startmark, $last - $this->startmark);
        }
        $this->startmark = $last;
        $this->curlang = $this->level[count($this->level)-1];
        $this->precontent = '';
    }

    /**
     * Extract the text, which falls is the specified language, in the XML formatted content.
     *
     * All text wrapped by sometag with lang="$lang" attribute, 
     * and those wrapped with no lang attribute will be included
     *
     * @param string $content The original full content
     * @param array $lang The language(s) of content wanted
     * @param array $options Same as $xlanguage->options['language']
     * @return string The extracted content
     */
    function filter(&$content, $lang, &$options) {
        $this->parser = xml_parser_create('UTF-8');
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 1);
        xml_set_element_handler($this->parser, 'start_tag', 'end_tag');
        $this->lang = $lang;
        $this->options = $options;
        $this->curlang = true;
        $this->level = array(true);
        $this->orig = '<xLg>' . ent2ncr($content) . '</xLg>';
        $this->content = '';
        $this->precontent = '';
        $this->startmark = 0;
        xml_parse($this->parser, $this->orig);

        if (xml_get_error_code($this->parser)) {
            $offset = xml_get_current_byte_index($this->parser) - 100;
            $startlen = xml_get_current_byte_index($this->parser) - $offset;
            if ($offset < 5) { $startlen -= 5 - $offset; $offset = 5; }
            if ($startlen < 0) { $startlen = 0; }

            if (defined(xLanguageTest)) {
                print xml_error_string(xml_get_error_code($this->parser)) . "\n";
                print "**" . substr($this->orig, $offset, $startlen) . "^^^^" . substr($this->orig, $offset + $startlen, 200 - $startlen) . "**\n";
            } else if (isset($options) && $options['parser']['log2'] && file_exists($options['parser']['log2']) && 
                filesize($options['parser']['log2']) < xLanguageParserLogSizeLimit) {
                $fd = fopen($options['parser']['log2'], 'a');
                $timenow = time();
                flock($fd, LOCK_EX);
                fseek($fd, 0, SEEK_END);
                fwrite($fd, 
                '<log>' . '<epoch>' . $timenow . '</epoch><time>' . htmlspecialchars(strftime("%c", $timenow + get_option('gmt_offset') * 3600)) . '</time>' .
                '<request>' . htmlspecialchars((empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) .'</request>' .
                '<error>' . htmlspecialchars(xml_error_string(xml_get_error_code($this->parser))) . '</error>' .
                '<lang>' . htmlspecialchars(implode(',', $lang)) . '</lang>' .
                '<precontent>' . htmlspecialchars( substr($this->orig, $offset, $startlen) ) . '</precontent>' .
                '<postcontent>' . htmlspecialchars( substr($this->orig, $offset + $startlen, 200 - $startlen) ) . '</postcontent>' .
                '</log>'
                );
                fclose($fd);
            }
            xml_parser_free($this->parser);
            return false;            
        } else {
            $ret = substr($this->content, 5, strlen($this->content) - 5 - 6);
        }
        xml_parser_free($this->parser);

        $old_ret = $ret;
        $allblocks = '(table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|map|area|blockquote|address|math|style|p|h[1-6]|hr)';
        $regex = '=<' . $allblocks . '[^>]*>\s*</\\1>=i';
        do {
            $old_ret = $ret;
            $ret = preg_replace($regex, '', $ret);
        } while ($ret != $old_ret); 
        
        return $ret;
    }
}


/**
 * xLanguageSBParser class
 * This extract the parts using the square bracket: [lang_...]...[/lang_...]
 *
 * @package xLanguage
 * @author Sam Wong
 * @copyright Copyright (C) 2008 Sam Wong
 **/
class xLanguageSBParser {
    function xLanguageSBParser() {
    }
    
    function match_callback($matches) {
        $this->used = 1;
        if (in_array($matches[1], $this->lang)) {
            return $matches[2];
        }
        return '';
    }

    /**
     * Extract the text, which falls is the specified language, in the text content.
     *
     * All text wrapped by sometag with [lang_$lang]...[/lang_$lang] attribute, 
     * and those wrapped with no lang attribute will be included
     *
     * @param string $content The original full content
     * @param array $lang The language(s) of content wanted
     * @param array $options Same as $xlanguage->options['language']
     * @return string The extracted content
     */
    function filter(&$content, $lang, &$options) {
        $this->parser = xml_parser_create('UTF-8');
        $this->lang = $lang;
        $this->options = $options;
        $this->used = 0;

        $prefix = $options['parser']['option_sb_prefix'];

        $content = preg_replace("|<p[^>]*>(\\[/?${prefix}_[^\\]]+\\])</p>|s", "$1", $content);
        $ret = preg_replace_callback("|\\[${prefix}([^\\]]+)\\](.*?)\\[/${prefix}\\1\\]|s", array(&$this, 'match_callback'), $content);
        
        if ($this->used) {
            $old_ret = $ret;
            $allblocks = '(table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|map|area|blockquote|address|math|style|p|h[1-6]|hr)';
            $regex = '=<' . $allblocks . '[^>]*>\s*</\\1>=i';
            do {
                $old_ret = $ret;
                $ret = preg_replace($regex, '', $ret);
            } while ($ret != $old_ret); 

            return $ret;
        } else {
            return false;
        }
    }
}

?>
