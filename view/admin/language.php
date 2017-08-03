<div class="wrap" style="overflow: auto">
	<h3><?php _e('Getting Started', 'xlanguage'); ?></h3>
    <?php if (!count($options['language'])) { ?>
    <p><?php _e('Please add at least one language in the section below.', 'xlanguage'); ?></p>
    <?php
    } else {
    ?>
    <div style="float: right; padding: 0 1em;">
    <img src="<?php echo $this->url() . '/images/usage.png' ?>" width="540" height="290" />
    <br/>
    <small><?php _e('Screenshot of the post writing editor') ?></small>
    </div>
    <p><?php _e('To tag the content in specified language, use the <em>xLanguage Toolbar</em> in the Rich Editor, or apply the standard <em>lang=".."</em> attribute in the related HTML tags.', 'xlanguage'); ?></p>
    <p><?php printf(__('For single lined content like Post Title, Categories Name and such, use the separator <span style="background: yellow">%s</span> to split them. This is called <em>single line filtering mode</em>', 'xlanguage'), $options['split']); ?></p>
    <address>
    <p><?php _e('Special thanks to <a href="http://leen.name/blog">Huizhe Xiao</a> for implementing the content filter feature and making version 2 possible.','xlanguage') ?></p>
    </address>
    <?php
    }
    ?>
</div>

<a name="p__feedback"></a>
<?php if (isset($messages['feedback'])) echo $messages['feedback']; ?>
<div class="wrap">
	<h3><?php _e('Quality Feedback System', 'xlanguage'); ?></h3>
    <p><?php _e('Enabling this system allows me to know how people use this plugin, which in turns enable me to refine this plugin and plan the next version better.', 'xlanguage'); ?></p>
    <p><?php _e('If enabled, only the configurable options of this plugins will be uploaded, and optionally your blog url.  No personal identifiable data will be collected.  The raw data will only be available to me and will not be transferred, but I reserve the rights to publish the statistical data that based on these raw data collected.', 'xlanguage'); ?></p>
	
	<form action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__feedback" method="post" accept-charset="utf-8">
    <fieldset class="options">
    <legend>Feedback System</legend>
    <table class="form-table">
		<tr>
			<th><?php _e('Feedback System:','xlanguage') ?><br /></th>
            <td>
            <input type="checkbox" name="enable" value="1" id="feedback_enable" <?php if ($options['feedback']['enable']) echo 'checked="checked"' ?> /><label for="feedback_enable"> <?php _e('Enable the feedback system') ?></label><br />
            <small><?php _e('This is one of the great way to show your support and making this plugin better, <a href="http://hellosam.net/contribute">among of the others</a>.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
			<th><?php _e('Blog URL:','xlanguage') ?><br /></th>
            <td>
            <input type="checkbox" name="expose" value="1" id="feedback_expose" <?php if ($options['feedback']['expose']) echo 'checked="checked"' ?> /><label for="feedback_expose"> <?php _e('Include the blog url when sending feedback') ?></label><br />
            <small><?php _e('The URL would not be made public, but merely let me know where is the xLanguage deployed. This is never revealed to any third party.', 'xlanguage') ?></small>
            </td>
        </tr>
		<tr>
			<th><?php _e('Last Feedback:','xlanguage') ?><br /></th>
            <td>
            <?php if (!empty($options['feedback']['last'])) { ?>
            <?php printf (__('The data has been sent at %1$s.<br>Result &#8212; %2$s'), gmstrftime("%c", $options['feedback']['last'] + (get_option('gmt_offset') * 3600)), $options['feedback']['last_status'] )  ?>
            <?php } else {
                    _e('No data has ever been sent yet.');
                }
            ?>
            </td>
        </tr>
        <?php if ($options['feedback']['enable']) { ?>
		<tr>
			<th><?php _e('Next Feedback') ?><br /></th>
            <td>
            <?php printf (__('The data will be sent after %1$s.'), gmstrftime("%c", $options['feedback']['next'] + (get_option('gmt_offset') * 3600)))  ?>
            </td>
        </tr>
        <?php } ?>
    </table>
    </fieldset>
    <table class="form-table">
		<tr>
			<th></th>
			<td class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Feedback Options', 'xlanguage'); ?>"/></td>
		</tr>
    </table>
    <input type="hidden" name="feedback">
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-feedback'); ?>
    </form>
</div>

<a name="p__update"></a>
<?php if (isset($messages['update'])) echo $messages['update']; ?>
<div class="wrap">
	<h3><?php _e('Languages', 'xlanguage'); ?></h3>
    <p><?php _e('This is a list that define the available languages on this blog.', 'xlanguage'); ?></p>
    
	<form action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__update" method="post" accept-charset="utf-8">
    <table border="0" class="widefat">
        <thead>
        <tr>
            <th style="text-align: left"><?php _e('Default', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Locale Code', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Name', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Time Format', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Date Format', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Text Missing Message', 'xlanguage') ?></th>
            <th style="text-align: left"><?php _e('Delete', 'xlanguage') ?></th>
        </tr>
        </thead>

        <?php
        $i = 0;
        foreach ($options['language'] as $key => $value) {
        ?>
        <tr <?php if ($i % 2 == 0) echo 'class="alternate"'; ?>>
            <td>
            <?php foreach (array(1 => __('User', 'xLanguage'), 2 => __('Search Engine', 'xLanguage')) as $availk => $availv) { ?>
                <input type="radio" name="default_<?php echo $availk ?>" id="_default_<?php echo $availk ?>_<?php echo $key ?>" value="<?php echo $key ?>" <?php echo $value['availability'] & $availk ? '' : 'disabled="disabled"' ?> <?php if ($key === $options['default2'][$availk]) echo 'checked="checked"' ?> />
                <label for="_default_<?php echo $availk ?>_<?php echo $key ?>"><?php echo $availv ?></label>
            <?php } ?>
            </td>
            <td><input type="hidden" name="language_<?php echo $i ?>_origcode" value="<?php echo htmlspecialchars(isset($value['origcode']) ? $value['origcode'] : $value['code']) ?>" />
                <input type="text" name="language_<?php echo $i ?>_code" size="5" value="<?php echo htmlspecialchars($value['code']) ?>" /></td>
            <td><input type="text" name="language_<?php echo $i ?>_name" size="10" value="<?php echo htmlspecialchars($value['name']) ?>" /></td>
            <td><input type="text" name="language_<?php echo $i ?>_timef" size="5" value="<?php echo htmlspecialchars($value['timef']) ?>" /></td>
            <td><input type="text" name="language_<?php echo $i ?>_datef" size="5" value="<?php echo htmlspecialchars($value['datef']) ?>" /></td>
            <td><input type="text" name="language_<?php echo $i ?>_missing" size="20" value="<?php echo htmlspecialchars($value['fallback']['_missing']) ?>" /></td>
            <td>
            <input type="checkbox" name="language_<?php echo $i ?>_delete" value="<?php echo $key ?>" />
            </td>
        </tr>
        <?php
        $i++;
        }
        ?>
		<tr>
        <?php if (count($options['language'])) { ?>
			<td colspan="7" class="submit"><input type="submit" class="button-primary" value="<?php _e('Update Languages', 'xlanguage'); ?>"/></td>
        <?php
        } else {
        ?>
            <td colspan="7" class="alternate"><?php _e('Please add at least one lanugage in the section below.', 'xlanguage'); ?></td>
        <?php
        }
        ?>
		</tr>
    </table>
    <input type="hidden" name="update">
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-update'); ?>
    </form>
</div>

<a name="p__add"></a>
<?php if (isset($messages['add'])) echo $messages['add']; ?>
<div class="wrap">
	<h3><?php _e('Add Language', 'xlanguage'); ?></h3>

	<form action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__add" method="post" accept-charset="utf-8">
    <fieldset class="options">
    <legend>Language</legend>
    <table class="form-table">
        <tr>
            <th><?php _e('Locale Code:', 'xlanguage') ?></th>
            <td>
            <input type="text" name="code" size="5" value="<?php echo htmlspecialchars($options['new_language']['code']) ?>"><br />
            <small><?php _e('Example: es. It should looks like en-us, zh-hk, de, jp, etc. The code choosen will have influence to browser for picking up the default font family.', 'xlanguage'); ?></small>
            </td>
        </tr>
		<tr>
            <th><?php _e('Name:', 'xlanguage') ?></th>
            <td>
            <input type="text" name="name" size="30" value="<?php echo htmlspecialchars($options['new_language']['name']) ?>"><br />
            <small><?php _e('Example: Español.  If you have two or more languages added, you may use "Spanish|Español|..." so that it displays "Spanish" for first language, "Español" for second and so on.', 'xlanguage'); ?></small>
            </td>
        </tr>
		<tr>
            <th><?php _e('Time Format:', 'xlanguage') ?></th>
            <td>
            <input type="text" name="timef" value="<?php echo (isset($options['new_language']) ? htmlspecialchars($options['new_language']['timef']) : __('G:i', 'xlanguage')); ?>" size="8"><br />
            <small><?php _e('The format string used for displaying time.  Refer to <a href="http://php.net/date">PHP Date function</a> for the code.', 'xlanguage'); ?></small>
            </td>
        </tr>
		<tr>
            <th><?php _e('Date Format:', 'xlanguage') ?></th>
            <td>
            <input type="text" name="datef" value="<?php echo (isset($options['new_language']) ? htmlspecialchars($options['new_language']['datef']) : __('j F Y', 'xlanguage')); ?>" size="8"><br />
            <small><?php _e('The format string used for displaying date.  Refer to <a href="http://php.net/date">PHP Date function</a> for the code.', 'xlanguage'); ?></small>
            </td>
        </tr>
		<tr>
            <th><?php _e('Text Missing Message:', 'xlanguage') ?></th>
            <td>
            <input type="text" name="missing" size="30" value="<?php echo htmlspecialchars($options['new_language']['missing']) ?>"><br />
            <small><?php _e('This message will be displayed in post content, excerpts if the content is not available in that language.', 'xlanguage'); ?></small>
            </td>
        </tr>
    </table>
    </fieldset>
    <table class="form-table">
		<tr>
			<th></th>
			<td class="submit"><input type="submit" class="button-primary" value="<?php _e('Add Language', 'xlanguage'); ?>"/></td>
		</tr>
    </table>
    <input type="hidden" name="add">
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-add'); ?>
    </form>
</div>
