<a name="p__update_filtering_metadata"></a>
<?php if (isset($messages['update_filtering_metadata'])) echo $messages['update_filtering_metadata']; ?>

<div class="wrap">
	<h3><?php _e('Rebuilding Display Filtering Metadata', 'xlanguage'); ?></h3>
    <p><?php _e('Please wait while the metadata for all posts are being rebuilt. Your browser will be refreshed automatically to rebuild all of them.', 'xlanguage'); ?></p>

    <p>Rebuilding...<br />
    <?php
    foreach($posts as $post)
        echo htmlspecialchars($post->post_title). " (#{$post->ID}). ";
    ?>
    </p>

    <form id="filter_metadata_form" action="<?php echo $this->url($_SERVER['REQUEST_URI']) ?>#p__update_filtering_metadata" method="post" accept-charset="utf-8">
        <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('xLanguage-update_mixmatch'); ?>
        <input type="hidden" name="do_update_filtering_metadata" />
        <input type="hidden" name="do_update_filtering_metadata_minid" value="<?php echo $minid;?>" />
        <input type="submit" value="<?php _e("Javascript is disabled. Please click here to continue updating the metadata", 'xlanguage')?>" />
    </form>
    <script type="text/javascript">
    jQuery(function() {
        var form = jQuery('#filter_metadata_form')[0];
        if (form.submit && setTimeout) {
            jQuery(form).hide();
            setTimeout(function(){ form.submit(); }, 200);
        }
    });
    </script>
    
</div>
