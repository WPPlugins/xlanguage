<?php /* To customize, you can copy this file to YOUR_THEME_DIR/view/xlanguage/ and the theme's one will be used instead. */ ?>
<ul>
<?php
foreach ($langs as $code => $lang) {
    $langd = &$options['language'][$code];
    $name = wp_localization($langd['name']);
    if (!count($langd['show'])) continue;

    $img = $this->get_external_file_url("images/${code}.png");
    $imgactive = $this->get_external_file_url("images/${code}-active.png");
    if (!$imgactive) $imgactive = $img;
    if ($img) $img = <<<EOF
<img src="$img" alt="$name" title="$name" /> 
EOF;
    else '';
    if ($imgactive) $imgactive = <<<EOF
<img src="$imgactive" alt="$name" title="$name - Active" /> 
EOF;
    else '';

    if ($language === $code) {
?>
<li class="language_item current_language_item"><?php echo $imgactive ?><?php echo $name ?></li>
<?php } else { ?>
<li class="language_item"><a href="<?php echo $lang['link'] ?>"><?echo $img ?><?php echo $name ?></a></li>
<?php
    }
}
?>
</ul>
