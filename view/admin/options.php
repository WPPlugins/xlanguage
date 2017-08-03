<div class="wrap">
<p><?php _e('The flexibility comes at the cost of complexity.  The <a href="http://hellosam.net/project/xlanguage/">usage guide</a> explains each options in details as well as the usage scenarios.') ?></p>
</div>

<a name="p__options"></a>
<?php if (isset($messages['options'])) echo $messages['options']; ?>
<div class="wrap">
	<h3><?php _e('Options', 'xlanguage'); ?></h3>
    
	<form action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__options" method="post" accept-charset="utf-8">
    <fieldset class="options">
    <legend>Viewing</legend>
    <table class="form-table">
		<tr>
			<th><?php _e('Explicit Redirection:','xlanguage') ?><br /></th>
            <td>
            <input type="radio" name="redirect" value="0" id="redirect_0" <?php if ($options['redirect'] == 0) echo 'checked="checked"' ?> /><label for="redirect_0"> <?php _e('Never') ?></label><br />
            <input type="radio" name="redirect" value="1" id="redirect_1" <?php if ($options['redirect'] == 1) echo 'checked="checked"' ?> /><label for="redirect_1"> <?php _e('Only if WP-Cache (or WP-SuperCache) is enabled [Recommend]') ?></label><br />
            <input type="radio" name="redirect" value="2" id="redirect_2" <?php if ($options['redirect'] == 2) echo 'checked="checked"' ?> /><label for="redirect_2"> <?php _e('Always') ?></label><br />
            <small><?php _e('This configures if we will redirect the user to a link with explicit language selection (.../lang/en...), if he visited one that does not.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
			<th><?php _e('Language Preferences Detection:','xlanguage') ?><br /></th>
            <td>
            <table><tr><td style="vertical-align: top">
            <input type="radio" name="pref_detection" value="<?php echo xLanguagePrefDetectionDefault ?>" id="pref_detection_255" <?php if ($options['pref_detection'] == xLanguagePrefDetectionDefault) echo 'checked="checked"' ?> /><label for="pref_detection_255"> <?php _e('Auto [Recommend]') ?></label>
            <br /><?php _e('(All enabled or URL only if WP-SuperCache is enabled)') ?>
            </td><td style="vertical-align: top">
            <input type="radio" name="pref_detection" value="0" id="pref_detection_0" <?php if ($options['pref_detection'] != xLanguagePrefDetectionDefault) echo 'checked="checked"' ?> /><label for="pref_detection_0"> <?php _e('Customize') ?></label><br />
            <input type="checkbox" name="pref_detections[]" value="1" id="pref_detection_1" <?php if ($options['pref_detection'] != xLanguagePrefDetectionDefault && $options['pref_detection'] & 1) echo 'checked="checked"' ?> /><label for="pref_detection_1"> <?php _e('URL') ?></label><br />
            <input type="checkbox" name="pref_detections[]" value="2" id="pref_detection_2" <?php if ($options['pref_detection'] != xLanguagePrefDetectionDefault && $options['pref_detection'] & 2) echo 'checked="checked"' ?> /><label for="pref_detection_2"> <?php _e('Cookie') ?></label><br />
            <input type="checkbox" name="pref_detections[]" value="4" id="pref_detection_4" <?php if ($options['pref_detection'] != xLanguagePrefDetectionDefault && $options['pref_detection'] & 4) echo 'checked="checked"' ?> /><label for="pref_detection_4"> <?php _e('User\'s Browser Preference') ?></label><br />
            </td></tr></table>
            <small><?php _e('This configures how user\'s language preference is detected.', 'xlanguage') ?></small>
            </td>
        </tr>
    </table>
    </fieldset>
    <fieldset class="options">
    <legend>Permalinks</legend>
    <p><?php printf(__('This only does matter if you have enabled <a href="options-permalink.php">%s</a>'), __('Permalinks')) ?></a></p>
    <table class="form-table">
		<tr>
			<th><?php _e('Primary Appending Position:','xlanguage') ?><br /></th>
            <td>
            <input type="radio" name="permalink_mode" value="<?php echo xLanguagePermalinkPrefix ?>" id="permalink_mode_0" <?php if ($options['permalink_mode'] == xLanguagePermalinkPrefix) echo 'checked="checked"' ?> /><label for="permalink_mode_0"> <?php _e('Prefix') ?>
            <small><?php _e('looks like http://example.com/blogurl/lang/en/archives/123. This has better compability with other plugins', 'xlanguage') ?></small></label><br />
            <input type="radio" name="permalink_mode" value="<?php echo xLanguagePermalinkPostfix ?>" id="permalink_mode_1" <?php if ($options['permalink_mode'] == xLanguagePermalinkPostfix) echo 'checked="checked"' ?> /><label for="permalink_mode_1"> <?php _e('Postfix') ?>
            <small><?php _e('looks like http://example.com/blogurl/archives/123/lang/en', 'xlanguage') ?></small></label><br />
            <small>WARNING: Postfix mode is known to be not working with Wordpress 2.7 paged comments mode and potentially causing conflicts with many other plugins. Please test throughoutly before deployment.</small>
            </td>
        </tr>
		<tr>
			<th><?php _e('Supported Mode:','xlanguage') ?><br /></th>
            <td>
            <input type="checkbox" name="permalink_support[]" value="<?php echo xLanguagePermalinkPrefix ?>" id="permalink_support_0" <?php if ($options['permalink_support'] & xLanguagePermalinkPrefix) echo 'checked="checked"' ?> /><label for="permalink_support_0"> <?php _e('Prefix') ?></label><br />
            <input type="checkbox" name="permalink_support[]" value="<?php echo xLanguagePermalinkPostfix ?>" id="permalink_support_1" <?php if ($options['permalink_support'] & xLanguagePermalinkPostfix) echo 'checked="checked"' ?> /><label for="permalink_support_1"> <?php _e('Postfix') ?></label><br />
            <small><?php _e('Your blog will be reachable with the supported mode', 'xlanguage') ?></small><br />
            </td>
        </tr>

		<tr>
			<th><?php _e('Redirection on Non-Primary Mode:','xlanguage') ?><br /></th>
            <td>
            <input type="radio" name="permalink_redirect" value="1" id="permalink_redirect_1" <?php if (!empty($options['permalink_redirect'])) echo 'checked="checked"' ?> /><label for="permalink_redirect_1"> <?php _e('Yes, redirected with HTTP 301 Permantent') ?></label><br />
            <input type="radio" name="permalink_redirect" value="0" id="permalink_redirect_0" <?php if (empty($options['permalink_redirect'])) echo 'checked="checked"' ?> /><label for="permalink_redirect_0"> <?php _e('No, serve the page as is.') ?></label><br />
            </td>
        </tr>
    </table>
    </fieldset>
    <fieldset class="options">
    <legend>Composing</legend>
    <p><?php _e('When using XHTML parser, content is expected to be marked using &lt;span lang="..."&gt; tag, while the Square Bracket expects to find [lang_...]...[/lang...] tags in the content.') ?></p>
    <table class="form-table">
		<tr>
			<th><?php _e('Single Mode Splitter:','xlanguage') ?><br /></th>
            <td>
            <input type="text" name="split" size="5" value="<?php echo htmlspecialchars($options['split']) ?>"><br />
            <small><?php _e('In single line filtering mode, the text is splitted by this splitter.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
			<th><?php _e('Parser:','xlanguage') ?><br /></th>
            <td>
            <input type="radio" name="parser_mode" value="1" id="parser_mode_a" <?php if (count($options['parser']['mode']) == 1 && $options['parser']['mode'][0] == 1) echo 'checked="checked"' ?> /><label for="parser_mode_a"> <?php _e('XHTML') ?></label><br />
            <input type="radio" name="parser_mode" value="2" id="parser_mode_b" <?php if (count($options['parser']['mode']) == 1 && $options['parser']['mode'][0] == 2) echo 'checked="checked"' ?> /><label for="parser_mode_b"> <?php _e('Sqaure Bracket') ?></label><br />
            <input type="radio" name="parser_mode" value="1,2" id="parser_mode_c" <?php if (count($options['parser']['mode']) == 2 && $options['parser']['mode'][0] == 1 && $options['parser']['mode'][1] == 2) echo 'checked="checked"' ?> /><label for="parser_mode_c"> <?php _e('XHTML, then Square Bracket if the content is not a valid XHTML document.') ?></label><br />
            <input type="radio" name="parser_mode" value="2,1" id="parser_mode_d" <?php if (count($options['parser']['mode']) == 2 && $options['parser']['mode'][0] == 2 && $options['parser']['mode'][1] == 1) echo 'checked="checked"' ?> /><label for="parser_mode_d"> <?php _e('Square Bracket, then XHTML if no square bracket tag were found.') ?></label><br />
            </td>
        </tr>
		<tr>
			<th><?php _e('Square Bracket Syntax:','xlanguage') ?><br /></th>
            <td>
            <code>[<input type="text" name="parser_option_sb_prefix" size="5" value="<?php echo htmlspecialchars($options['parser']['option_sb_prefix']) ?>"
                onkeyup="document.getElementById('sb_prefix_demo_a').innerHTML = this.value" onchange="document.getElementById('sb_prefix_demo_a').innerHTML = this.value"><em><?php _e('localecode') ?></em>]<?php _e('Sample Text') ?>[/<span id="sb_prefix_demo_a"><?php echo htmlspecialchars($options['parser']['option_sb_prefix']) ?></span><em><?php _e('localecode') ?></em>]</code><br />
            <small><?php _e('The text matching the above syntax will be recognized by the Square Bracket parser.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
			<th><?php _e('Toolbar Mode:','xlanguage') ?><br /></th>
            <td>
            <input type="radio" name="parser_default" value="1" id="parser_default_1" <?php if ($options['parser']['default'] == 1) echo 'checked="checked"' ?> /><label for="parser_default_1"> <?php _e('XHTML') ?></label><br />
            <input type="radio" name="parser_default" value="2" id="parser_default_2" <?php if ($options['parser']['default'] == 2) echo 'checked="checked"' ?> /><label for="parser_default_2"> <?php _e('Sqaure Bracket') ?></label><br />
            <small><?php _e('This configures the code that will be inserted by toolbar in composing Tiny MCE editor.', 'xlanguage') ?></small>
            </td>
        </tr>
    </table>
    </fieldset>
    <fieldset class="options">
    <legend>General</legend>
    <table class="form-table">
		<tr>
			<th><?php _e('Contribution Reminder:','xlanguage') ?><br /></th>
            <td>
            <input type="checkbox" name="contribution" value="1" id="contribution" <?php if (!empty($options['contribution'])) echo 'checked="checked"' ?>><label for="contribution"> Hide</label><br />
            <small><?php _e('I hereby testify that I have supported this plugin. Please visit <a href="http://hellosam.net/contribute">the contribution page</a> to see how you can support this.', 'xlanguage') ?></small>
            </td>
		</tr>
    </table>
    </fieldset>
    <table class="form-table">
		<tr>
			<th></th>
			<td class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Options', 'xlanguage'); ?>"/></td>
		</tr>
    </table>
    <input type="hidden" name="options">
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-options'); ?>
    </form>
</div>

<a name="p__hook"></a>
<?php if (isset($messages['hook'])) echo $messages['hook']; ?>
<div class="wrap">
	<h3><?php _e('Filter Hooks', 'xlanguage'); ?></h3>
    <p><?php _e('This is a list of the filters that this plugin is currently hooked to. The first textbox is the priority that the filters will be hooked at.', 'xlanguage'); ?></p>
    <p><?php _e('If a filter is hooked by this plugin, this plugin will apply the language logic over the content passed from the caller.', 'xlanguage'); ?></p>
    
	<form action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__hook" method="post" accept-charset="utf-8">
    <fieldset class="options">
    <legend>Hooks</legend>
    <table class="form-table">
        <tr>
            <th>language</th>
            <td>
            <input type="text" name="hookpriority_language" size="3" value="<?php echo htmlspecialchars($options['hookpriority']['language']) ?>">
            <input type="text" name="hook_language" size="50" value="<?php echo htmlspecialchars(implode(',', $options['hook']['language'])) ?>"><br />
            <small><?php _e('Return the locale code of current preferred language, or leave it as is if preference cannot be determined.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
            <th>text</th>
            <td>
            <input type="text" name="hookpriority_text" size="3" value="<?php echo htmlspecialchars($options['hookpriority']['text']) ?>">
            <input type="text" name="hook_text" size="50" value="<?php echo htmlspecialchars(implode(',', $options['hook']['text'])) ?>"><br />
            <small><?php _e('Apply language extraction logic to the content if language preference is determined.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
            <th>textsingle</th>
            <td>
            <input type="text" name="hookpriority_textsingle" size="3" value="<?php echo htmlspecialchars($options['hookpriority']['textsingle']) ?>">
            <input type="text" name="hook_textsingle" size="50" value="<?php echo htmlspecialchars(implode(',', $options['hook']['textsingle'])) ?>"><br />
            <small><?php _e('Like text, but single mode is always applied, even with multiline content.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
            <th>textlink</th>
            <td>
            <input type="text" name="hookpriority_textlink" size="3" value="<?php echo htmlspecialchars($options['hookpriority']['textlink']) ?>">
            <input type="text" name="hook_textlink" size="50" value="<?php echo htmlspecialchars(implode(',', $options['hook']['textlink'])) ?>"><br />
            <small><?php _e('If HTML hyperlink (&lt;a&gt;) is passed, all link content will be filtered by the text filter aforementioned, leaving non-link content intact.  If no hyperlink area passed, treat as text.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
            <th>link</th>
            <td>
            <input type="text" name="hookpriority_link" size="3" value="<?php echo htmlspecialchars($options['hookpriority']['link']) ?>">
            <input type="text" name="hook_link" size="50" value="<?php echo htmlspecialchars(implode(',', $options['hook']['link'])) ?>"><br />
            <small><?php _e('Expecting an URL, and construct a new URL that includes the language preference selection.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
            <th>date_format</th>
            <td>
            <input type="text" name="hookpriority_date_format" size="3" value="<?php echo htmlspecialchars($options['hookpriority']['date_format']) ?>">
            <input type="text" name="hook_date_format" size="50" value="<?php echo htmlspecialchars(implode(',', $options['hook']['date_format'])) ?>"><br />
            <small><?php _e('Returns a specified time format according to the determined language preference', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
            <th>time_format</th>
            <td>
            <input type="text" name="hookpriority_time_format" size="3" value="<?php echo htmlspecialchars($options['hookpriority']['time_format']) ?>">
            <input type="text" name="hook_time_format" size="50" value="<?php echo htmlspecialchars(implode(',', $options['hook']['time_format'])) ?>"><br />
            <small><?php _e('Returns a specified time format according to the determined language preference.', 'xlanguage') ?></small>
            </td>
        </tr>
    </table>
    </fieldset>
    <table class="form-table">
		<tr>
			<th></th>
			<td class="submit"><input type="submit" class="button-primary" value="<?php _e('Update Hooks', 'xlanguage'); ?>"/></td>
		</tr>
    </table>
    <input type="hidden" name="hook">
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-hook'); ?>
    </form>
</div>

<a name="p__reset"></a>
<?php if (isset($messages['reset'])) echo $messages['reset']; ?>
<div class="wrap">
	<h3><?php _e('Reset', 'xlanguage'); ?></h3>
	
	<form action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__reset" method="post" accept-charset="utf-8">
    <fieldset class="options">
    <legend>Reset</legend>
    <table class="form-table">
		<tr>
			<th><?php _e('Reset to default:','xlanguage') ?><br /></th>
			<td><input type="checkbox" name="confirm_reset" id="confirm_reset" value="1"><label for="confirm_reset"> <?php _e('I confirm I want to reset all the configurable options to the default') ?></label></td>
		</tr>
    </table>
    </fieldset>
    <table class="form-table">
		<tr>
			<th></th>
			<td style="text-align: left" class="submit"><input type="submit" class="button-primary" value="<?php _e('Reset', 'xlanguage'); ?>"/></td>
		</tr>
    </table>
    <input type="hidden" name="reset">
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-reset'); ?>
    </form>
</div>
