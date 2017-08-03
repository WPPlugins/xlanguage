<div class="wrap">
<p><?php _e('The flexibility comes at the cost of complexity.  The <a href="http://hellosam.net/project/xlanguage/">usage guide</a> explains each options in details as well as the usage scenarios.') ?></p>
</div>

<a name="p__update_filtering"></a>
<?php if (isset($messages['update_filtering'])) echo $messages['update_filtering']; ?>
<div class="wrap">
	<h3><?php _e('Display Filtering', 'xlanguage'); ?></h3>

    <p><?php _e('By enabling this feature, all posts not written in a language of the reader will be hidden.','xlanguage')?></p>
    <p><?php _e('Please noted that if you have specified fallback for specified language combination, the fallback settings will be honored.','xlanguage')?></p>
    <form id="filter_form" action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__update_filtering" method="post" accept-charset="utf-8">
    <fieldset class="options">
    <legend>Filtering</legend>
    <table class="form-table">
		<tr>
			<th><?php _e('Content Filtering:','xlanguage') ?><br /></th>
			<td><input type="checkbox" <?php echo $options['query']['enable'] ? 'checked' : '' ?> id="filter_checkbox" name="filter" value="1"> <label for="filter_checkbox"> <?php _e('Enable');?></label></td>
		</tr>
		<tr id="filter_content_div">
			<th><?php _e('Filtering Tweak:','xlanguage') ?><br /></th>
			<td>
            <input type="checkbox" <?php echo $options['query']['enable_for']['feed'] ? 'checked' : ''   ?> id="filter_feed_checkbox"   name="filter_feed" value="1"
            /><label for="filter_feed_checkbox"> <?php _e('Apply filtering to feed', 'xlanguage');?></label><br/>
            <input type="checkbox" <?php echo $options['query']['enable_for']['search'] ? 'checked' : '' ?> id="filter_search_checkbox" name="filter_search" value="1"
            /><label for="filter_search_checkbox"> <?php _e('Apply filtering to search result', 'xlanguage');?></label><br/>
            <input type="checkbox" <?php echo $options['query']['enable_for']['post'] ? 'checked' : ''   ?> id="filter_post_checkbox"   name="filter_post" value="1"
            /><label for="filter_post_checkbox"> <?php _e('Apply filtering to other non-feed, non-search result list such as those by category or by date', 'xlanguage');?></label><br/>
            <input type="checkbox" <?php echo $options['query']['enable_for']['page'] ? 'checked' : ''   ?> id="filter_page_checkbox"   name="filter_page" value="1"
            /><label for="filter_page_checkbox"> <?php _e('Apply filtering to page list, such as Widget page list, Theme page list', 'xlanguage');?></label>
            </td>
		</tr>
    </table>
    </fieldset>
    <table class="form-table">
		<tr>
			<th></th>
            <td style="text-align: left" class="submit"><input type="submit" class="button-primary" value="<?php _e('Update Filtering Options', 'xlanguage'); ?>" /></td>
		</tr>
    </table>

    <input type="hidden" name="update_filtering" />
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-update_filtering'); ?>
    <script type="text/javascript">
    function update_filter_form() {
        if (jQuery("#filter_checkbox").is(":checked"))
            jQuery('#filter_content_div').show();
            else
            jQuery('#filter_content_div').hide();
    }
    if(jQuery)
    jQuery(function() {
        jQuery("#filter_checkbox").click(update_filter_form);
        update_filter_form();
    });
    </script>
    </form>
</div>

<? if ($options['query']['enable']) : ?>
<a name="p__update_filtering_metadata"></a>
<?php if (isset($messages['update_filtering_metadata'])) echo $messages['update_filtering_metadata']; ?>
<div class="wrap">
	<h3><?php _e('Display Filtering Metadata', 'xlanguage'); ?></h3>
    <form id="filter_metadata_form" action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__update_filtering_metadata" method="post" accept-charset="utf-8">
        <p><?php _e('In cases that you have changed any languages settings, or the posts are modified on the database directly, you will need to rebuild the metadata so that the filtering can work correctly.', 'xlanguage'); ?></p>
        <p><?php _e('Depending on numbers of posts you have, the rebuilding will take a few seconds to a minute. The process is segmented and the browser will refresh automatically to complete the rebuilding.', 'xlanguage'); ?></p>
        <input type="hidden" name="do_update_filtering_metadata" />
        <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-do_update_filtering_metadata'); ?>
        <input type="hidden" name="do_update_filtering_metadata_forceupdate" value="1" />
        <table class="form-table">
            <tr>
                <th></th>
                <td style="text-align: left" class="submit"><input type="submit" class="button-primary" value="<?php _e('Rebuild Metadata', 'xlanguage'); ?>" /></td>
            </tr>
        </table>
    </form>
    <script type="text/javascript">
    if(jQuery)
    jQuery(function() {
        if(/auto_update_filtering_metadata_forceupdate/i.test(location.href)){
            jQuery('#do_update_filtering_metadata_forceupdate').attr("checked", true);
            jQuery('#filter_metadata_form')[0].submit();
        }else if(/auto_update_filtering_metadata/i.test(location.href))
            jQuery('#filter_metadata_form')[0].submit();
        });
    </script>
</div>
<? endif; ?>
<a name="p__update_presentation"></a>
<?php if (isset($messages['update_presentation'])) echo $messages['update_presentation']; ?>
<div class="wrap">
	<h3><?php _e('Presentation', 'xlanguage'); ?></h3>
    <p><?php _e('The language can be marked available for only user, or search engine crawler, or both.  This can be controlled with the checkboxes in the <strong>Avalibility</strong> column.', 'xlanguage'); ?></p>
    <p><?php _e('You might choose to use another <strong>Theme</strong> instead of the default one for some languages.', 'xlanguage'); ?></p>
    
	<form action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__update_presentation" method="post" accept-charset="utf-8">
    <table border="0" class="widefat">
        <thead>
        <tr>
            <th style="text-align: left"><?php _e('Locale Code', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Availability', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Theme', 'xlanguage') ?></th>
        </tr>
        </thead>
        <?php
        $i = 0;
        foreach ($options['language'] as $key => $value) {
        ?>
        <tr <?php if ($i % 2 == 0) echo 'class="alternate"'; ?>>
            <td><input type="hidden" name="language_<?php echo $i ?>_origcode" value="<?php echo htmlspecialchars($value['code']) ?>" /><?php echo htmlspecialchars($value['code']) ?></td>
            <td>
            <?php foreach (array(1 => __('User', 'xLanguage'), 2 => __('Search Engine', 'xLanguage')) as $availk => $availv) { ?>
                <input type="checkbox" name="language_<?php echo $i ?>_availability[]" id="_language_<?php echo $i ?>_availability_<?php echo $availk ?>" value="<?php echo $availk ?>" <?php echo $value['availability'] & $availk ? 'checked="checked"' : '' ?> />
                <label for="_language_<?php echo $i ?>_availability_<?php echo $availk ?>"><?php echo $availv ?></label>
            <?php } ?>
            </td>
            <td><input type="text" name="language_<?php echo $i ?>_theme" size="10" value="<?php echo htmlspecialchars($value['theme']) ?>" /></td>
        </tr>
        <?php
        $i++;
        }
        ?>
		<tr>
        <?php if (count($options['language'])) { ?>
			<td colspan="3" class="submit"><input type="submit" class="button-primary" value="<?php _e('Update Presentation', 'xlanguage'); ?>" /></td>
        <?php
        } else {
        ?>
        <td colspan="3" class="alternate"><?php _e('Please add at least one lanugages in the <em>Language</em> section.', 'xlanguage'); ?></td>
        <?php
        }
        ?>
		</tr>
    </table>
    <input type="hidden" name="update_presentation" />
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-update_presentation'); ?>
    </form>
</div>

<a name="p__update_mixmatch"></a>
<?php if (isset($messages['update_mixmatch'])) echo $messages['update_mixmatch']; ?>
<div class="wrap">
	<h3><?php _e('Mix and Match', 'xlanguage'); ?></h3>
    <p><?php _e('When a page is filter and displayed in a particular language, the html elements\' lang attributes will change to <strong>Shown As</strong>.  This code will also be returned by the "language" filter, as well as affecting the MO files chosen by i10n localization.', 'xlanguage'); ?></p>
    <p><?php _e('When displaying a page with particular language, all text tagged with language specified in <strong>Show</strong> will be displayed to the user. (Does not apply to single line filtering mode)', 'xlanguage'); ?></p>
    <p><?php _e('When determine the which languages are available in a post or page (e.g. for display filtering, or when xlanguage_post_other_langs() is called), text tagged with one language will be treated as available as in languages specified in its <strong>Available As</strong>.', 'xlanguage'); ?></p>
    <p><?php _e('In single line filtering mode, the content in position (start from 0) specified in <strong>Position</strong> will be extracted and displayed. Use <em>-1</em> to show all.', 'xlanguage'); ?></p>
    <p><?php _e('The relationship of <strong>Locale code</strong> to <strong>Show</strong>, <strong>Available As</strong> is usually symmetric, if not the same.', 'xlanguage'); ?></p>
    <p><?php _e('Example: Consider Language B and C (which is an localized version of B), where reader of B can read B, and reader of C can read B and C. Sometimes I want to write different version for reader of B and C.' .
                ' To faciliate this, I need to create an extra Language A, as well as B, and C as usual, and set the following:<br />' .
                'Show-A=empty, AvailAs-A=B+C, Show-B=A+B, AvailAs-B=B, Show-C=A+C, AvailAs-C=C and ShownAs-*=A.<br />' .
                'Then I will write my blog in Lang A normally, and B + C if I want to take extra care for both.  If you consider B being Chinese and C being Cantonese, then this will makes a lot of sense.<br/>' .
                'In this particular application, you might want to make sure the <strong>Locale Code</strong> for Lang A is something unmatchable like xx-*, as it will be used for matching the browser preference.:','xlanguage'); 
    ?></p>
    <p><?php _e('When a post is not available under one particular language, xLanguage can fallback to the other languages in the <strong>Fallback</strong> list in order, optionally showing the text message associated. ' .
                'A new row for input will appear after a new one is entered and saved.:','xlanguage') ?></p>
    <p><?php _e('If you are using content filtering, the fallback aginst a specific language has a higher priority will instead showing the message followed by the main text instead of being hidden. Specifying the fallback message for <i>_missing</i> will not trump the filtering behavior and the message will only be used in single post mode instead.','xlanguage') ?></p>
    
	<form action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__update_mixmatch" method="post" accept-charset="utf-8">
    <table border="0" class="widefat">
        <thead>
        <tr>
            <th style="text-align: left"><?php _e('Locale Code', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Shown As', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Show', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Available As', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Position', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Fallback', 'xlanguage') ?></th>
        </tr>
        </thead>

        <?php
        $i = 0;
        foreach ($options['language'] as $key => $value) {
        ?>
        <tr <?php if ($i % 2 == 0) echo 'class="alternate"'; ?>>
            <td><input type="hidden" name="language_<?php echo $i ?>_origcode" value="<?php echo htmlspecialchars($value['code']) ?>" /><?php echo htmlspecialchars($value['code']) ?></td>
            <td><input type="text" name="language_<?php echo $i ?>_shownas" size="5" value="<?php echo htmlspecialchars($value['shownas']) ?>" /></td>
            <td><input type="text" name="language_<?php echo $i ?>_show" size="10" value="<?php echo htmlspecialchars(implode(',', $value['show'])) ?>" /></td>
            <td><input type="text" name="language_<?php echo $i ?>_availas" size="10" value="<?php echo htmlspecialchars(implode(',', $value['availas'])) ?>" /></td>
            <td><input type="text" name="language_<?php echo $i ?>_pos" size="3" value="<?php echo htmlspecialchars($value['pos']) ?>" /></td>
            <td>

            <?php foreach ($value['fallback'] as $lkey => $lvalue) { if ($lkey == '_missing') break; ?>
            <input type="text" name="language_<?php echo $i ?>_fallback_key[]" size="5" value="<?php echo htmlspecialchars($lkey) ?>" />
            <input type="text" name="language_<?php echo $i ?>_fallback_value[]" size="20" value="<?php echo htmlspecialchars($lvalue) ?>" /><br />
            <?php } ?>
            <input type="text" name="language_<?php echo $i ?>_fallback_key[]" size="5" value="" />
            <input type="text" name="language_<?php echo $i ?>_fallback_value[]" size="20" value="" /><br />
            <input type="text" name="language_<?php echo $i ?>_fallback_key[]" size="5" value="_missing" readonly="readonly" style="color: grey" />
            <input type="text" name="language_<?php echo $i ?>_fallback_value[]" size="20" value="<?php echo htmlspecialchars($value['fallback']['_missing']) ?>" />
            </td>
        </tr>
        <?php
        $i++;
        }
        ?>
		<tr>
        <?php if (count($options['language'])) { ?>
			<td colspan="6" class="submit"><input type="submit" class="button-primary" value="<?php _e('Update Mix and Match', 'xlanguage'); ?>" /></td>
        <?php
        } else {
        ?>
        <td colspan="6" class="alternate"><?php _e('Please add at least one lanugages in the <em>Language</em> section.', 'xlanguage'); ?></td>
        <?php
        }
        ?>
		</tr>
    </table>
    <input type="hidden" name="update_mixmatch" />
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-update_mixmatch'); ?>
    </form>
</div>
