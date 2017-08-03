<?php
/*
xLanguagePluginAdmin - The Admin logics for the xLanguage plugin

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

define('xLanguagePostmetaUpdateCountPerRequest', 100);

/**
 * xLanguagePluginAdmin class
 *
 * @package xLanguage
 * @author Sam Wong
 * @copyright Copyright (C) 2008 Sam Wong
 **/
class xLanguagePluginAdmin extends xLanguagePluginBase
{
    /**
     * Constructor of the Admin Interface, called by the main xLanguage plugin
     *
     * @return void
     **/
    function xLanguagePluginAdmin(&$options)
    {
        $this->options = $options;
        $this->plugin_base = dirname(__FILE__);
        $this->plugin_name = 'xlanguage.php';
    
        $this->default_options =
        array(
            'contribution' => false,
            'default' => '',
            'default2' => array( 1 => '', 2 => '' ),

            'split' => '|',
            'redirect' => xLanguageRedirectAuto,
            'pref_detection' => xLanguagePrefDetectionDefault,
            'feedback' => array(
                'enable' => false, 'expose' => false,
                'last' => 0, 'last_status' => '', 'next' => 0
            ),
            
            'query' => array(     
                'enable' => false,
                'enable_for' => array(
                    'feed' => true,
                    'search' => true,
                    'post' => true,
                    'page' => true,
              )
            ),

            'parser' => array(
                'mode' => array(xLanguageParserXHtml),
                'default' => xLanguageParserXHtml,
                'option_sb_prefix' => 'lang_',
                'log2' => '' # To be filled later
            ),
            
            'language' => array(),

            'permalink_mode' => xLanguagePermalinkPrefix, // Where should the /lang/xx placed in permalink
            'permalink_support' => xLanguagePermalinkPrefix, // The mode that supported
            'permalink_redirect' => true, // If user reach the page with non primary mode, use 301 to redirect

            'hook' => array(
                'language' => array('locale', 'pre_option_rss_language'),
                'text' => array(
                    'localization',
                    'language',
                    'bloginfo','get_bloginfo_rss',
                    'the_content','the_content_rss','the_excerpt','the_excerpt_rss','single_post_title','the_title',
                    'term_name','term_name_rss', // Terms
                    'link_name','link_notes', // Blogroll
                    'single_tag_title', // Tag
                    'single_cat_title','list_cats', // Category
                    'widget_text'
                    ),
                'textsingle' => array(
                    'term_description','term_description_rss','link_description','category_description','widget_title'
                    ),
                'textlink' => array(
                    'the_tags', 'wp_generate_tag_cloud', // Tag
                    'the_category' // Category
                ),
                'link' => array(
                    'the_permalink','post_link','page_link','day_link','month_link','year_link','feed_link','category_link',
                    'post_comments_feed_link','trackback_url','author_feed_link','category_feed_link','tag_feed_link',
                    'get_comments_pagenum_link','get_pagenum_link'
                ),
                'date_format' => array('pre_option_date_format'),
                'time_format' => array('pre_option_time_format')
            ),
            'hookpriority' => array(
                'language' => 18,
                'text' => 18,
                'textlink' => 18,
                'textsingle' => 18,
                'link' => 18,
                'date_format' => 18,
                'time_format' => 18
            ),
            'structversion' => xLanguageOptionsStructVersion
        );

        if (strstr($_SERVER['REQUEST_URI'], 'post.php') || strstr($_SERVER['REQUEST_URI'], 'post-new.php') || strstr($_SERVER['REQUEST_URI'], 'page-new.php') || strstr($_SERVER['REQUEST_URI'], 'page.php'))
        {
            $this->add_action('admin_head', 'admin_head_post');
        }
    }

    function plugins_loaded()
    {
        if (isset($_SERVER['QUERY_STRING'])) {
            if (strstr($_SERVER['QUERY_STRING'], 'xlanguage-tinymce-css')) {
                /* Should we check any capabilities? */
                auth_redirect();
                $this->admin_tinymce_css();
                exit;
            } else if (strstr($_SERVER['QUERY_STRING'], 'xlanguage-parserlog-file') && current_user_can('manage_options')) {
                $this->admin_screen_parserlog_file();
                exit;
            } else if (strstr($_SERVER['QUERY_STRING'], 'xlanguage-parserlog-xslt') && current_user_can('manage_options')) {
                $this->admin_screen_parserlog_xslt();
                exit;
            }
        } 

        if (strstr($_SERVER['REQUEST_URI'], $this->plugin_name))
        {
            $this->add_action('admin_head');
        }

        $this->add_filter('admin_menu');
        $this->add_filter ('contextual_help', 'contextual_help', 10, 2);

        if (isset($this->options) && $this->options['feedback']['enable'] && $this->options['feedback']['next'] < time()) {
            $this->feedback();
        }

        if (isset($this->options) && $this->options['parser']['log2'] && !file_exists($this->options['parser']['log2'])) {
            $fd = fopen($this->options['parser']['log2'], 'w');
            fclose($fd);
        }
        
        $this->add_action('save_post', 'save_post', 10, 2);
    }


    /**
     * Send feedback data
     */
    function feedback() {
        global $cookiehash;
        
        $this->options['feedback']['last'] = time();
        $this->options['feedback']['next'] = $this->options['feedback']['last'] + 3600*24 * 7;
        
        $data = 'options=' . urlencode(serialize($this->options));

        $req  = "POST /xlanguage.php HTTP/1.1\r\n";
        $req .= "Host: feedback.hellosam.net\r\n";
        if (!empty($this->options['feedback']['expose'])) {
            $req .= "Referrer: " . get_option('home') . "\r\n";
        }
        $req .= "User-Agent: PluginFeedback Plugin/xlanguage FeedbackHash/$cookiehash\r\n";
        $req .= "Connection: close\r\n";
        $req .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
        $req .= "Content-Length: " . strlen($data) . "\r\n\r\n";
        $req .= $data;
        
        $last_status = '';
        $s = @fsockopen('feedback.hellosam.net', 80, $errno, $errstr, 5);
        if ($s) {
            stream_set_timeout($s, 5);
            @fwrite($s, $req, strlen($req));
            @fflush($s);
            if (!feof($s)) {
                $last_status .= @fread($s, 8192);
                // Ok. We don't do while loop to prevent lockup
            }
            fclose($s);
        } else {
            $last_status = "Error: $errstr ($errno)";
        }
        $last_status = preg_replace("/^.*?\|(.*?)\|.*?$/s", '\1', $last_status);
        $this->options['feedback']['last_status'] = htmlspecialchars(strip_tags($last_status));
        if (!defined('xLanguageTest')) update_option('xlanguage_options', $this->options);
    }
    

    /**
     * Performs first-time activation
     *
     * @return void
     **/
    function activate()
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();

        if (empty($this->options)) {
            $this->options = $this->default_options;

            $ud = wp_upload_dir();
            if (!empty($ud['error'])) die($ud['error']);
            $this->options['parser']['log2'] = $ud['path'] . '/xlanguage-parser-' . rand(10000, getrandmax()) . rand(10000, getrandmax()) . '.log';
            
            update_option('xlanguage_options', $this->options);
        }
    }
    
    
    /**
     * WordPress hook to add to the management menu
     *
     * @return void
     **/
    function admin_menu()
    {
        add_options_page( __('xLanguage', 'xlanguage'), __('xLanguage', 'xlanguage'), 6, $this->plugin_name, array(&$this, 'admin_screen'));
    }
    
    
    /**
     * WordPress hook to add CSS/JS to the Admin Interface - Options section
     *
     * @return void
     **/	
    function admin_head()
    {
        $this->render_admin('head');
    }


    /**
     * WordPress hook to add CSS to the Tiny MCE editor
     *
     * @return void
     **/	
    function admin_tinymce_css()
    {
        $this->render_admin('tinymce_css', array('options' => $this->options));
    }
    
    /**
     * WordPress hook to add CSS/JS to the Admin Interface - Post section
     *
     * @return void
     **/	
    function admin_head_post()
    {
        if (user_can_richedit())
            $this->render_admin('head_post', array('options' => $this->options));
    }
    
    /**
     * save_post action hook
     *
     * @return void
     */
    function save_post($id, $post){
        $this->update_postmeta($post);
    }
    
    /**
     * Update the available language list for current post
     *
     * @return void
     */
    function update_postmeta($post){
        global $xlanguage;
        // We don't care about the revision updates.
        if ($post->post_type == 'revision') return;

        $spliter = xLanguagePostMetaAvailableLanguageListSpliter;
        $post_langs = array();
        foreach ($this->options['language'] as $k => $v)
            if($xlanguage->contains_lang($post->post_content, $k))
                $post_langs[] = $k;
        $post_langs_str = $spliter . implode($spliter, $post_langs) . $spliter;
        add_post_meta($post->ID, xLanguageQueryMetadataKey, $post_langs_str, true) or update_post_meta($post->ID, xLanguageQueryMetadataKey, $post_langs_str);
    }

    /**
     * Create language metadata for non-revision posts if there isn't one.
     * @param int $min_id [in, out] 
     *   Only post with an ID bigger than this value will have its metadata updated. 
     *   This value will be set to the biggest ID that has been updated.
     * @param int $limit Limit
     * @return Array posts updated
     */
    function update_postmeta_all(&$min_id, $limit) {
        global $wpdb;
        $query = "
            SELECT posts.ID, posts.post_content, posts.post_title FROM {$wpdb->posts} AS posts
            LEFT JOIN {$wpdb->postmeta} as available_languages on (
                available_languages.post_id = posts.ID AND
                available_languages.meta_key = '" . xLanguageQueryMetadataKey . "'
            ) WHERE 
            posts.post_type <> 'revision' AND 
            available_languages.meta_value IS NULL AND
            posts.ID > $min_id
            ORDER BY posts.ID ASC
            LIMIT 0, $limit
        ";
        $posts = $wpdb->get_results($query);
        foreach($posts as $post) {
            $this->update_postmeta($post);
            $min_id = max($min_id, $post->ID);
        }
        return $posts;
    }
    
    /**
     * Clear all posts' metadata
     * @return void
     */
    function clear_postmeta_all() {
        global $wpdb;
        $query = "
            DELETE FROM {$wpdb->postmeta} WHERE meta_key = '" . xLanguageQueryMetadataKey . "'
        ";
        $wpdb->get_results($query);
    }
    
    /**
     * Get the alert HTML for updating language postmeta
     * This function will return empty string when $this->options['query']['enable'] is not true
     * @return string Alert HTML
     */
    function get_alert_for_updating_postmeta() {
        if ($this->options['query'] && $this->options['query']['enable'])
            return __('<div>You should <a href="options-general.php?page=xlanguage.php&sub=advanced&%s#auto_update_filtering_metadata_forceupdate">regenerate the language postmeta</a> to make sure your posts are displayed correctly.</div>', 'xlanguage');
        return '';
    }
    
    /**
     * Validate the options.  It is not design to expect the unexpected though.
     *
     * @param array $options The options in the format of get_option()
     * @return string Human readable error each seperated by new-line
     */
    function validate_options($options) 
    {
        $error = '';
        foreach ($options['language'] as $lang) {
            if (!preg_match('/^[a-z]{2,4}(?:-[a-z]{2,4}){0,2}$/', $lang['code'])) {
                $error .= "\n" . sprintf(__("'%s' is not a valid locale code. It must be lowercase only and in the format of xx[-xx]*, where each group of x must be between 2 to 4 characters long."), 
                htmlspecialchars($lang['code']));
            }
            if (! is_int($lang['pos']) || $lang['pos'] < -1 || $lang['pos'] >= count($options['language']) ) {
                $error .= "\n" . sprintf(__("The language '%s' has an invalid 'Position' parameter."), htmlspecialchars($lang['code']));
            }

            if (!preg_match('/^[a-z]{2,4}(?:-[a-z]{2,4}){0,2}$/', $lang['shownas'])) {
                $error .= "\n" . sprintf(__("The <strong>Shown As</strong> '%2\$s' in the language '%1\$s' is not a valid locale code. It must be lowercase only and in the format of xx[-xx]*, where each group of x must be between 2 to 4 characters long."), htmlspecialchars($lang['code']), htmlspecialchars($lang['shownas']));
            }

            if (! is_array($lang['show'])) {
                $error .= "\n" . sprintf(__("The language '%s' has an invalid <strong>Show</strong> parameter.  Please delete it and re-create, and report this bug."), htmlspecialchars($lang['code']));
            } else {
                foreach ($lang['show'] as $show) {
                    if (!array_key_exists($show, $options['language'])) {
                        $error .= "\n" . sprintf(__("The language '%s' has an invalid <strong>Show</strong> parameter."), htmlspecialchars($lang['code']));
                        break;
                    }
                }
            }
            if (! is_array($lang['availas'])) {
                $error .= "\n" . sprintf(__("The language '%s' has an invalid <strong>Avail As</strong> parameter.  Please delete it and re-create, and report this bug."), htmlspecialchars($lang['code']));
            } else {
                foreach ($lang['availas'] as $show) {
                    if (!array_key_exists($show, $options['language'])) {
                        $error .= "\n" . sprintf(__("The language '%s' has an invalid <strong>Avail As</strong> parameter."), htmlspecialchars($lang['code']));
                        break;
                    }
                }
            }
            
            foreach ($lang['fallback'] as $lkey => $lvalue) {
                if ($lkey != '_missing' && !isset($options['language'][$lkey])) {
                    $error .= "\n" . sprintf(__("The language '%s' has an invalid <strong>fallback</strong> choice with locale code of '%s'.", 'xlanguage'), htmlspecialchars($origcode), htmlspecialchars($lkey));
                }
                if ($lkey == $origcode) {
                    $error .= "\n" . sprintf(__("The language '%s' has a <strong>fallback</strong> entry which has the same locale code as itself.", 'xlanguage'), htmlspecialchars($origcode), htmlspecialchars($lkey));
                }
            }
        }

        if (empty($options['permalink_mode'])) {
                $error .= "\n" . __('The permalinks <strong>primary appending position</strong> cannot be empty.', 'xlanguage');
        } else {
            if (empty($options['permalink_support']) || ($options['permalink_support'] & $options['permalink_mode']) == 0) {
                $error .= "\n" . __('The permalinks <strong>supported mode</strong> must include the <strong>primary appending position</strong>.', 'xlanguage');
            }
        }
        
        if (strlen($options['split']) == 0) {
            $error .= "\n" . __("The <strong>Single Mode Splitter</strong> cannot be empty.", 'xlanguage');
        }
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $options['parser']['option_sb_prefix'])) {
            $error .= "\n" . __("The <strong>Square Bracket Syntax</strong> must be consists of a-z, A-Z, 0-9, - (dash) and _ (underscore) only.", 'xlanguage');
        }
        return $error;
    }

    function contextual_help ($help, $screen)
    {
        if ($screen == 'settings_page_xlanguage')
        {
            $help .= '<h5>' . __('xLanguage Help') . '</h5><div class="metabox-prefs">';
            $help .= '<a href="http://hellosam.net/project/xlanguage/">'.__('xLanguage Project Page and Usage Guide', 'drain-hole').'</a>';
            $help .= '</div>';
        }
        return $help;
    }

    /**
     * Display the admin 'options' page
     *
     * @return void
     **/
    function admin_screen()
    {
        // Subpage
        $url = explode('?', $_SERVER['REQUEST_URI']);
        $url = $url[0];
        $url .= '?page='.$_GET['page'];

        $sub = isset($_GET['sub']) ? $_GET['sub'] : 'language';
        $this->render_admin('submenu', array ('url' => $url, 'sub' => $sub));

        if ($sub == 'advanced') {
            $this->admin_screen_advanced();
        } else if ($sub == 'options') {
            $this->admin_screen_options();
        } else if ($sub == 'parserlog') {
            $this->admin_screen_parserlog();
        } else if ($sub == 'parserlog_file') {
            $this->admin_screen_parserlog_file();
        } else if ($sub == 'parserlog_xslt') {
            $this->admin_screen_parserlog_xslt();
        } else {
            $this->admin_screen_language();
        }
    }
    

    /**
     * Display the admin 'Language' page
     *
     * @return void
     **/
    function admin_screen_language()
    {
        global $wp_rewrite;
        $options = $this->options;
        $messages = array();
        
        if (isset($_POST['feedback'])) {
            check_admin_referer('xLanguage-feedback');
            $options['feedback']['enable'] = !empty($_POST['enable']);
            $options['feedback']['expose'] = !empty($_POST['expose']);
            
            update_option('xlanguage_options', $options);
            $messages['feedback'] = $this->capture_message(__('Feedback options has been updated.', 'xlanguage'));
        }
        if (isset($_POST['update'])) {
            check_admin_referer('xLanguage-update');
            $options['default2'][1] = stripslashes($_POST['default_1']);
            $options['default2'][2] = stripslashes($_POST['default_2']);
            $first_good_newcode = '';
            $error = '';
            $dupe_retry = 0;
            $dupe_reset = 0;
            
            do {
                $options['origlanguage'] = $options['language'];
                $options['language'] = array();

                $i = 0;
                $j = 0;
                if ($dupe_retry) $dupe_reset = 1;
                while (isset($_POST["language_${i}_code"])) {
                    $origcode = str_replace(',', '', stripslashes($_POST["language_${i}_origcode"]));
                    $newcode = $dupe_retry ? $origcode : str_replace(',', '', stripslashes($_POST["language_${i}_code"]));
                    $_POST["language_${i}_code"] = addslashes($newcode);
                    if (! isset($_POST["language_${i}_delete"]) && isset($options['origlanguage'][$origcode])) {
                        if (isset($options['language'][$newcode])) {
                            $error = sprintf(__("\nLocale code '%s' appears more than once."), $newcode);
                            $dupe_retry = 1;
                            break;
                        }
                        $options['language'][$newcode] = $options['origlanguage'][$origcode];
                        foreach (array(
                            'origcode' => $origcode,
                            'code' => $newcode,
                            'name' => stripslashes($_POST["language_${i}_name"]),
                            'timef' => stripslashes($_POST["language_${i}_timef"]),
                            'datef' => stripslashes($_POST["language_${i}_datef"]),
                            'missing' => stripslashes($_POST["language_${i}_missing"]),
                            'pos' => ($options['origlanguage'][$origcode]['pos'] >= 0) ? $options['origlanguage'][$origcode]['pos'] - ($i - $j) : $options['origlanguage'][$origcode]['pos'],
                        ) as $k => $v) {
                            $options['language'][$newcode][$k] = $v;
                        }

                        if (empty($first_good_newcode)) $first_good_newcode = $newcode;
                        $j++;
                    }
                    $i++;
                }
            } while ($dupe_retry && !$dupe_reset);

            // Replace all the langcode to language_x
            $i = 0;
            while (isset($_POST["language_${i}_code"])) {
                $tempcode = "language_${i}";
                $origcode = stripslashes($_POST["language_${i}_origcode"]);

                if ($options['default'] == $origcode) $options['default'] = $tempcode;
                foreach ($options['default2'] as $key => $lang) {
                    if ($lang == $origcode) $options['default2'][$key] = $tempcode;
                }
                foreach ($options['language'] as $key => $lang) {
                    if ($lang['shownas'] == $origcode) $options['language'][$key]['shownas'] = $tempcode;
                    foreach ($lang['show'] as $k => $v) { if ($v == $origcode) $options['language'][$key]['show'][$k] = $tempcode; }
                    foreach ($lang['availas'] as $k => $v) { if ($v == $origcode) $options['language'][$key]['availas'][$k] = $tempcode; }
                    $fallback = array();
                    foreach ($lang['fallback'] as $k => $v) { $fallback[$k == $origcode ? $tempcode : $k] = $v; }
                    $options['language'][$key]['fallback'] = $fallback;
                }
                $i++;
            }

            // Now replace all the language_x to new langcode
            $i = 0;
            while (isset($_POST["language_${i}_code"])) {
                $origcode = "language_${i}";
                $tempcode = stripslashes($_POST["language_${i}_code"]);

                if ($options['default'] == $origcode) $options['default'] = $tempcode;
                foreach ($options['default2'] as $key => $lang) {
                    if ($lang == $origcode) $options['default2'][$key] = $tempcode;
                }
                if (! isset($_POST["language_${i}_delete"])) {
                    foreach ($options['language'] as $key => $lang) {
                        if ($lang['shownas'] == $origcode) $options['language'][$key]['shownas'] = $tempcode;
                        foreach ($lang['show'] as $k => $v) { if ($v == $origcode) $options['language'][$key]['show'][$k] = $tempcode; }
                        foreach ($lang['availas'] as $k => $v) { if ($v == $origcode) $options['language'][$key]['availas'][$k] = $tempcode; }
                        $fallback = array();
                        foreach ($lang['fallback'] as $k => $v) { $fallback[$k == $origcode ? $tempcode : $k] = $v; }
                        $options['language'][$key]['fallback'] = $fallback;
                    }
                } else {
                    foreach ($options['language'] as $key => $lang) {
                        if ($lang['shownas'] == $origcode) $options['language'][$key]['shownas'] = '';
                        $options['language'][$key]['show'] = array_diff($lang['show'], array($origcode));
                        $options['language'][$key]['availas'] = array_diff($lang['availas'], array($origcode));
                        unset($options['language'][$key]['fallback'][$origcode]);
                    }
                }
                $i++;
            }
            
            if (count($options['language']) > 0) {
                if (!array_key_exists($options['default2'][1], $options['language'])) {
                    $options['default'] = $options['default2'][1] = $first_good_newcode;
                }
                if (!array_key_exists($options['default2'][2], $options['language'])) {
                    $options['default2'][2] = $first_good_newcode;
                }
            }

            $error .= $this->validate_options($options);
                
            if (empty($error)) {
                foreach ($options['language'] as $key => $lang) {
                    unset($options['language'][$key]['origcode']);
                }
                update_option('xlanguage_options', $options);
                $wp_rewrite->flush_rules();
                $messages['update'] = $this->capture_message(__('Languages has been updated.', 'xlanguage').$this->get_alert_for_updating_postmeta());
            } else {
                $messages['update'] = $this->capture_message( sprintf( __("Languages cannot be updated because:%s", 'xlanguage'), nl2br($error) ), '', 'error' );
            }
        }
        if (isset($_POST['add']))
        {
            check_admin_referer('xLanguage-add');
            $error = '';
            $newcode = stripslashes($_POST['code']);
            $lang = array
            (
                'code' => $newcode,
                'name' => stripslashes($_POST['name']),
                'timef' => stripslashes($_POST['timef']),
                'datef' => stripslashes($_POST['datef']),
                'pos' => count($options['language']),
                'shownas' => $newcode,
                'availability' => xLanguageLangAvailabilityUser + xLanguageLangAvailabilityBot,
                'show' => array($newcode),
                'availas' => array($newcode),
                'fallback' => array('_missing' => stripslashes($_POST['missing'])),
            );
            if (count($options['language']) == 0) {
                $options['default'] = $lang['code'];
                $options['default2'][1] = $lang['code'];
                $options['default2'][2] = $lang['code'];
            }

            if (isset($options['language'][$newcode])) {
                $error .= sprintf(__("\nLocale code '%s' appears more than once."), $newcode);
            } else {
                $options['language'][$lang['code']] = $lang;
            }

            $error .= $this->validate_options($options);
            if (empty($error)) {
                update_option ('xlanguage_options', $options);
                $wp_rewrite->flush_rules();
                $messages['add'] = $this->capture_message( __('A new language has been added.', 'xlanguage').$this->get_alert_for_updating_postmeta());
            } else {
                $options['new_language'] = $lang;
                $messages['add'] = $this->capture_message( sprintf( __("Languages cannot be added because:%s", 'xlanguage'), nl2br($error) ), '', 'error' );
            }
        }

        $this->render_admin('language', array ('options' => $options, 'messages' => $messages));
    }

    /**
     * Display the admin 'Language (Advanced)' page
     *
     * @return void
     **/
    function admin_screen_advanced()
    {
        $options = $this->options;
        $messages = array();
        if (isset($_POST['update_presentation'])) {
            check_admin_referer('xLanguage-update_presentation');
            $i = 0;
            $error = '';
            $warning = '';
            while (isset($_POST["language_${i}_origcode"])) {
                $origcode = stripslashes($_POST["language_${i}_origcode"]);
                if (isset($options['language'][$origcode])) {
                    $options['language'][$origcode]['availability'] = 0;
                    if (count($options['language'][$origcode]['show'])) {
                        if (isset($_POST["language_${i}_availability"])) {
                            foreach ($_POST["language_${i}_availability"] as $v) {
                                $options['language'][$origcode]['availability'] |= (int) $v;
                            }
                        }
                    } else {
                        if (isset($_POST["language_${i}_availability"])) 
                            $error .= "\n" . sprintf(__("The language '%s' cannot be made available to visitors because it <strong>show</strong>s nothing.  You can resolve this by changing the <strong>Show</strong> parameter in the <strong>Mix and Match</strong> section.", 'xlanguage'), htmlspecialchars($origcode));
                    }
                    for ($a = 1; $a <= 2; $a *= 2) {
                        if ($options['default2'][$a] == $origcode && ($options['language'][$origcode]['availability'] & $a) == 0) {
                            $warning .= "\n" . sprintf(__("You should <a href=\"options-general.php?page=xlanguage.php#p__update\">change the language default</a> because '%s' is no longer available for some visitors.", 'xlanguage'), htmlspecialchars($origcode));
                            break;
                        }
                    }
                    $options['language'][$origcode]['theme'] = stripslashes($_POST["language_${i}_theme"]);
                    if (!empty($options['language'][$origcode]['theme']) && 
                        !file_exists(get_theme_root() . '/' . $options['language'][$origcode]['theme'] . '/index.php') ) {
                        $error .= "\n" . sprintf(__("The theme specificied for language '%s' does not exists.  Double check the folder name, make sure even the case matches, or change to another one.", 'xlanguage'), htmlspecialchars($origcode));
                    }
                }
                $i++;
            }

            $error .= $this->validate_options($options);
            if (empty($error)) {
                update_option('xlanguage_options', $options);
                if ($warning) {
                    $messages['update_presentation'] = $this->capture_message( sprintf(__('Presentation settings have been updated, with warnings:%s', 'xlanguage'), nl2br($warning)) );
                } else {
                    $messages['update_presentation'] = $this->capture_message(__('Presentation settings have been updated.', 'xlanguage'));
                }
            } else {
                $messages['update_presentation'] = $this->capture_message( sprintf( __("Presentation settings cannot be updated because:%s", 'xlanguage'), nl2br($error) ), '', 'error' );
            }
        }
        if (isset($_POST['update_filtering'])){
            $error = "";
          
            if (!isset($options['query']))
                $options['query'] = array();
            
            $options['query']['enable'] = intval($_POST['filter']);
          
            if (!is_array($options['query']['enable_for']))
                $options['query']['enable_for'] = array();
          
            foreach(array('page', 'feed', 'post', 'search') as $o)
                $options['query']['enable_for'][$o] = intval($_POST["filter_$o"]);
          
            $error .= $this->validate_options($options);
            if (empty($error)) {
                update_option('xlanguage_options', $options);
                // $this->get_alert_for_updating_postmeta() require $this->options['query']['enable'] to be set
                // so let's assign the value back to the class variable now
                $this->options = $options; 
                $messages['update_filtering'] = $this->capture_message(__('Language filter options have been updated.', 'xlanguage') . $this->get_alert_for_updating_postmeta());
            } else {
                $messages['update_filtering'] = $this->capture_message( sprintf( __("Language filter options cannot be updated because:%s", 'xlanguage'), nl2br($error) ), '', 'error' );
            }
        }
        if (isset($_POST['do_update_filtering_metadata'])){
            $forceupdate = $_POST['do_update_filtering_metadata_forceupdate'] ? 1 : 0;
            $minid = intval($_POST['do_update_filtering_metadata_minid']);
            if ($forceupdate)
                $this->clear_postmeta_all();
          
            $posts = $this->update_postmeta_all($minid, xLanguagePostmetaUpdateCountPerRequest);
            if ($posts & count($posts) > 0 ){
                $this->render_admin('update_filtering_metadata', array ('posts' => $posts, 'options' => $options, 'messages' => $messages));
                return;
            } else {
                $messages['update_filtering_metadata'] = $this->capture_message(__("The metadata has been rebuilt.", 'xlanguage'));
            }
        }
        if (isset($_POST['update_mixmatch'])) {
            check_admin_referer('xLanguage-update_mixmatch');
            $i = 0;
            $error = '';
            $warning = '';
            while (isset($_POST["language_${i}_origcode"])) {
                $origcode = stripslashes($_POST["language_${i}_origcode"]);
                if (isset($options['language'][$origcode])) {
                    $options['language'][$origcode]['pos'] = (int) stripslashes($_POST["language_${i}_pos"]);
                    $options['language'][$origcode]['shownas'] = stripslashes($_POST["language_${i}_shownas"]);
                    $options['language'][$origcode]['show'] = strlen(stripslashes($_POST["language_${i}_show"])) > 0 ? array_unique(array_map( 'trim', explode(',', stripslashes($_POST["language_${i}_show"])) )) : array();
                    $options['language'][$origcode]['availas'] = strlen(stripslashes($_POST["language_${i}_availas"])) > 0 ? array_unique(array_map( 'trim', explode(',', stripslashes($_POST["language_${i}_availas"])) )) : array();
                    if (!count($options['language'][$origcode]['show'])) {
                        if ($options['language'][$origcode]['availability'] > 0) {
                            $warning .= "\n" . sprintf(__("The language '%s' is no longer available to visitors because it <strong>show</strong>s nothing.", 'xlanguage'), htmlspecialchars($origcode));
                        }
                        $options['language'][$origcode]['availability'] = 0;
                    } else {
                        if ($options['language'][$origcode]['availability'] == 0) {
                            $warning .= "\n" . sprintf(__("The language '%s' has something to show but not available to visitors.  You can resolve this by changing the <strong>Availability</strong> parameter in the <strong>Presentation</strong> section.", 'xlanguage'), htmlspecialchars($origcode));
                        }
                    }
                    
                    $options['language'][$origcode]['fallback'] = array();
                    $fallback = array_combine($_POST["language_${i}_fallback_key"], $_POST["language_${i}_fallback_value"]);
                    $fallback_missing  = 0;
                    foreach ($fallback as $ulkey => $lvalue) {
                        $lkey = stripslashes($ulkey);
                        if (!empty($lkey)) {
                            $options['language'][$origcode]['fallback'][$lkey] = stripslashes($lvalue);
                        }
                        if ($lkey == '_missing') { $fallback_missing = 1; break; }
                    }
                    if (!$fallback_missing)
                        $error .= "\n" . sprintf(__("The language '%s' must have '_missing' in the last fallback choice.", 'xlanguage'), htmlspecialchars($origcode));

                }
                $i++;
            }

            $error .= $this->validate_options($options);
            if (empty($error)) {
                update_option('xlanguage_options', $options);
                if ($warning) {
                    $messages['update_mixmatch'] = $this->capture_message( sprintf(__('Languages has been updated, with warnings:%s', 'xlanguage'), nl2br($warning)).$this->get_alert_for_updating_postmeta() );
                } else {
                    $messages['update_mixmatch'] = $this->capture_message(__('Languages has been updated.', 'xlanguage').$this->get_alert_for_updating_postmeta());
                }
            } else {
                $messages['update_mixmatch'] = $this->capture_message( sprintf( __("Languages cannot be updated because:%s", 'xlanguage'), nl2br($error) ), '', 'error' );
            }
        }

        $this->render_admin('advanced', array ('options' => $options, 'messages' => $messages));
    }

    /**
     * Display the admin 'Options' page
     *
     * @return void
     **/
    function admin_screen_options()
    {
        global $wp_rewrite;
        $options = $this->options;
        $messages = array();
        
        if (isset($_POST['options'])) {
            check_admin_referer('xLanguage-options');
            $error = '';

            $options['redirect'] = (int) $_POST['redirect'];
            $options['pref_detection'] = (int) $_POST['pref_detection'];
            if ($options['pref_detection'] != xLanguagePrefDetectionDefault) {
                $options['pref_detection'] = 0;
                foreach ($_POST['pref_detections'] as $v) {
                    $options['pref_detection'] |= (int) $v;
                }
            }
            if ($options['pref_detection'] == 0) {
                $warning .= "\n" . __("xLanguage will not work at all with no language preferences detection algorithm selected");
            } else if (!($options['pref_detection'] & xLanguagePrefDetectionLink)) {
                $warning .= "\n" . __("Permalink will not work as expected if Link is not used in language preferences detection.");
            }

            $options['permalink_mode'] = (int) $_POST['permalink_mode'];
            $options['permalink_support']  = 0;
            foreach ($_POST['permalink_support'] as $v) {
                $options['permalink_support'] |= (int) $v;
            }
            $options['permalink_redirect'] = (int) $_POST['permalink_redirect'];
            
            $options['split'] = trim(stripslashes($_POST['split']));
            
            $options['parser']['mode'] = array_map('trim', explode(',',stripslashes($_POST['parser_mode'])));
            $options['parser']['option_sb_prefix'] = stripslashes($_POST['parser_option_sb_prefix']);
            $options['parser']['default'] = (int) stripslashes($_POST['parser_default']);

            $options['contribution'] = !empty($_POST['contribution']);
            
            $error .= $this->validate_options($options);

            if (empty($error)) {
                update_option('xlanguage_options', $options);
                $wp_rewrite->flush_rules();
                if ($warning) {
                    $messages['options'] = $this->capture_message( sprintf(__('Options has been saved, with warnings:%s', 'xlanguage'), nl2br($warning)).$this->get_alert_for_updating_postmeta() );
                } else {
                    $messages['options'] = $this->capture_message(__('Options has been saved.', 'xlanguage').$this->get_alert_for_updating_postmeta());
                }
            } else {
                $messages['options'] = $this->capture_message( sprintf( __("Options cannot be saved because:%s", 'xlanguage'), nl2br($error) ), '', 'error' );
            }
        }
        if (isset($_POST['hook'])) {
            check_admin_referer('xLanguage-hook');
            $keys = array_keys($options['hook']);
            foreach ($keys as $key) {
                if (isset($_POST["hook_$key"])) {
                    $options['hook'][$key] = array_unique(array_map( 'trim', explode(',', stripslashes($_POST["hook_$key"])) ));
                    $options['hookpriority'][$key] = (int) trim(stripslashes($_POST["hookpriority_$key"]));
                }
            }            
            update_option('xlanguage_options', $options);
            $messages['hook'] = $this->capture_message(__('Hooks have been updated.', 'xlanguage'));
        }
        if (isset($_POST['reset'])) {
            check_admin_referer('xLanguage-reset');
            if (!empty($_POST['confirm_reset'])) {
                $options = $this->default_options;

                $options['contribution'] = $this->options['contribution'];
                $options['feedback']['enable'] = $this->options['feedback']['enable'];
                $options['feedback']['expose'] = $this->options['feedback']['expose'];
                
                $this->options = $options;
                update_option('xlanguage_options', $options);
                $wp_rewrite->flush_rules();
                $messages['reset'] = $this->capture_message(__('The settings have been reset.', 'xlanguage'));
            } else {
                $messages['reset'] = $this->capture_message(__('The settings was not reset because the confirmation checkbox was not checked.'), '', 'error' );
            }
        }

        $this->render_admin('options', array ('options' => $options, 'messages' => $messages));
    }

    /**
     * Display the admin 'Parser Log' page
     *
     * @return void
     **/
    function admin_screen_parserlog()
    {
        $options = $this->options;
        $messages = array();
        
        if (isset($_POST['parserlog'])) {
            check_admin_referer('xLanguage-parserlog');
            $error = '';
            
            if (!empty($_POST['confirm_clear'])) {
                $fd = fopen($this->options['parser']['log2'], 'w');
                fclose($fd);
            
                $messages['parserlog'] = $this->capture_message(__('The parser log has been cleared.', 'xlanguage'));
            } else {
                $messages['parserlog'] = $this->capture_message(__('The parser log was not cleared because the confirmation checkbox was not checked.'), '', 'error' );
            }
        }

        if (filesize($options['parser']['log2']) > xLanguageParserLogSizeLimit)
            $messages['parserlog_oversize'] = $this->capture_message(__('The log file has already grown to the limit and no new error will be logged.  You can resolve this by clearing the log.'), '', 'error' );
        
        $this->render_admin('parserlog', array ('options' => $options, 'messages' => $messages, 'oversize' => $oversize));
    }

    /**
     * In the admin 'Parser Log' page - Display the Log XHTML
     *
     * @return void
     **/
    function admin_screen_parserlog_file()
    {
        $url = explode('?', $_SERVER['REQUEST_URI']);
        $url = $url[0];
        $url .= '?page='.$_GET['page'] . '&xlanguage-parserlog-xslt';
        
        header("Content-type: text/xml");
        print '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        print '<?xml-stylesheet type="text/xsl" href="' . htmlspecialchars($url) . '"?>' . "\n";
        print "<ParserLog>";
        $fd = fopen($this->options['parser']['log2'], 'r');
        flock($fd, LOCK_SH);
        fseek($fd, 0, SEEK_SET);
        fpassthru($fd);
        fclose($fd);
        print "</ParserLog>";
    }
    
    /**
     * In the admin 'Parser Log' page - Display the Log XSLT
     *
     * @return void
     **/
    function admin_screen_parserlog_xslt()
    {
        header("Content-type: text/xslt+xml");
        $this->render_admin('parserlog_xslt', array ('options' => $options, 'messages' => $messages));
    }
}

