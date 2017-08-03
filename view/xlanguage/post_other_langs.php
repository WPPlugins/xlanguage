<?php /* To customize, you can copy this file to YOUR_THEME_DIR/view/xlanguage/ and the theme's one will be used instead. */ ?>
<span class="language_item">
<?php
foreach ($langs as $code => $lang) {
    $langd = &$options['language'][$code];
    $name = wp_localization($langd['name']);
    if (count(array_diff($lang['availby'], $options['language'][$language]['show'])) == 0) continue; // Skip the language current shown

    $img = $this->get_external_file_url("images/${code}.png");
    if ($img) $img = <<<EOF
<img src="$img" alt="$name" title="$name" />
EOF;
    else '';
?>
 <a href="<?php echo $lang['link'] ?>"><?php echo $img ?></a>
<?php
}
?>
</span>
