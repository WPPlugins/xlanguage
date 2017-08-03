<a name="p__parserlog"></a>
<?php if (isset($messages['parserlog'])) echo $messages['parserlog']; ?>
<?php if (isset($messages['parserlog_oversize'])) echo $messages['parserlog_oversize']; ?>
<div class="wrap">
	<h3><?php _e('Parser Log', 'xlanguage'); ?></h3>
        
    <fieldset class="options">
    <legend>Viewing</legend>
    <p><?php _e('The error encountered during XHTML parsing is logged below.') ?></p>

    <iframe id="xlanguage_log_frame" src="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>&amp;xlanguage-parserlog-file"></iframe>
    </fieldset>
    
	<form action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__parserlog" method="post" accept-charset="utf-8">
    
    <fieldset class="options">
    <legend>Clear</legend>
    <table border="0" class="form-table">
		<tr>
			<th><?php _e('Clear Parser Log:','xlanguage') ?><br /></th>
			<td><input type="checkbox" name="confirm_clear" id="confirm_clear" value="1"><label for="confirm_clear"> <?php _e('I confirm I want to clear the parser log') ?></label></td>
		</tr>
		<tr>
			<th></th>
			<td class="submit"><input type="submit" class="button-primary" value="<?php _e('Clear', 'xlanguage'); ?>"/></td>
		</tr>
    </table>
    </fieldset>
    
    <input type="hidden" name="parserlog" />
    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-parserlog'); ?>
    </form>
</div>

