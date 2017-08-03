<?php
/*
Template - xLanguage theme template function 

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
 * xLanguage Theme Template Functions
 *
 * @package xLanguage
 * @author Sam Wong
 * @copyright Copyright (C) Sam Wong
 **/
 
/**
 * Shows the list of available languages for the post/page
 * Template can be overriden with THEME_DIR/view/xlanguage/post_other_langs.php
 *
 * @return void The output is printed directly
 **/
function xlanguage_post_other_langs()
{
    global $xlanguage;
    global $post;

    $content = $post->post_content;
    $link = get_permalink();
    $langs = array();

    foreach ($xlanguage->options['parser']['mode'] as $mode) {
        if ($mode == xLanguageParserXHtml) {
            $result = preg_match_all('/<[^>]* lang="([^>]+)"(?: [^>]*|)*>/', $content, $match, PREG_PATTERN_ORDER);
        } else {
            $prefix = $xlanguage->options['parser']['option_sb_prefix'];
            $result = preg_match_all("/\\[${prefix}([^\\]]+)\\]/", $content, $match, PREG_PATTERN_ORDER);
        }
        if ($result) {
            $match = array_unique($match[1]);
            $match = array_intersect($match, array_keys($xlanguage->options['language']));
         
            foreach ($match as $lang) {
                foreach ($xlanguage->options['language'][$lang]['availas'] as $avail) {
                    if ($xlanguage->options['language'][$avail]['availability'] & $xlanguage->useragent) {
                        if (!array_key_exists($avail, $langs)) {
                            $langs[$avail] = array('link' => $xlanguage->filter_link_in_lang($link, $avail), 'availby' => array($lang));
                        } else {
                            $langs[$avail]['availby'][] = $lang;
                        }
                    }
                }
            }
        }
    }

    $xlanguage->render('post_other_langs', array('options' => $xlanguage->options, 'langs' => $langs, 'language' => $xlanguage->language));
}


/**
 * Shows the list of all available languages defined
 * Template can be overriden with THEME_DIR/view/xlanguage/list_langs.php
 *
 * @return void The output is printed directly
 **/
function xlanguage_list_langs()
{
    global $xlanguage;
    
    $link = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $langs = array();
    foreach ($xlanguage->options['language'] as $lang => $v) {
        if ($v['availability'] & $xlanguage->useragent)
            $langs[$lang] = array('link' => $xlanguage->filter_link_in_lang($link, $lang));
    }

    $xlanguage->render('list_langs', array('options' => $xlanguage->options, 'langs' => $langs, 'language' => $xlanguage->language));
}

/**
 * Get the locale code of the current viewing language
 *
 * @return Return the locale code or FALSE if it could not be determined
 **/
function xlanguage_current_language_code()
{
    global $xlanguage;
    return $xlanguage->filter_language(false);
}

/**
 * Get the language name of the current viewing language
 *
 * @return Return the language name in the representation of the current viewing language or FALSE if it could not be determined
 **/
function xlanguage_current_language()
{
    global $xlanguage;
    $lang = $xlanguage->filter_language(false);
    if ($lang) {
        return wp_localization($xlanguage->options['language'][$lang]['name']);
    }
    return $lang;
}


if (!function_exists('wp_localization')) {
    /**
     * Apply language localization logic over the text
     *
     * @return Return the part of text which is tagged as the current language
     **/
    function wp_localization($content) {
        return apply_filters('localization', $content);
    }
}

?>
