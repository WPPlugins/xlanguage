<?php
$color = array('#fbb','#bbf','#bfb','#fbf','#ffb');
$color_count = 0;
foreach ($options['language'] as $lang) {
    echo ".xlanguage-highlight *[lang=${lang['code']}] { background: ${color[$color_count]}; border-top: 2px solid ${color[$color_count]}; }\n";
    $color_count = ($color_count + 1 % count($color));
}
?>
