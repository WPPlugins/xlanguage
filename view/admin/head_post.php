<script type="text/javascript" charset="utf-8">
    var wp_base_xlanguage = '<?php echo $this->url () ?>/';
    var xlanguage_mode = <?php echo $options['parser']['default'] ?>;
    var xlanguage_sb_prefix = "<?php echo $options['parser']['option_sb_prefix'] ?>";
    var xlanguage_language = [ <?php echo implode(',', array_map( create_function('$v', 'return "\'${v[\'code\']}\'";'), $options['language'] ) ) ?> ];
</script>
<? if (!(function_exists('has_filter') && has_filter('mce_external_plugins'))) { ?>
<script src="<?php echo $this->url(); ?>/js/tinymce-plugin.js?ver=<?php echo xLanguageJavascriptVersion; ?>" type="text/javascript" charset="utf-8"></script>
<? } ?>
