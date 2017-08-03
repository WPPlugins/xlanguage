<div class="wrap">
<h2>xLanguage</h2>
<ul class="subsubsub">
  <li><a <?php if ($sub == 'language') echo 'class="current"'; ?>href="<?php echo $url ?>"><?php _e ('Language', 'xlanguage') ?></a> |</li>
  <li><a <?php if ($sub == 'advanced') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=advanced"><?php _e ('Language (Advanced)', 'xlanguage') ?></a> |</li>
  <li><a <?php if ($sub == 'options') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=options"><?php _e ('Options', 'xlanguage') ?></a> |</li>
  <li><a <?php if ($sub == 'parserlog') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=parserlog"><?php _e ('Parser Log', 'xlanguage') ?></a></li>
</ul>
</div>
<div class="clear"></div>

<?php if (empty($options['contribution'])) { ?>
<div class="confirm updated below-h2" id="contribution_box">
    <?php switch(rand(0,2)) {
    case 0:
        _e('<p>Free software does not come from nothing. Do you know you could keep me motivated in improving and writing more free software! <a href="http://hellosam.net/contribute">Come to see</a> how you can change the software ecosystem.</p>');
        break;
    case 1:
        _e('<p>Food is cheap over here. It is cost me less than USD 2 for a lunch. <a href="http://hellosam.net/contribute">Come and buy me a few lunches!</a></p>');
        break;
    case 2:
        _e('<p>There are four ways you can support this. 1. <a href="http://hellosam.net/project/xlanguage">Bug and improvement feedback</a>. 2. Enable Quality Feedback System. 3. Spread the word. 4. <a href="http://hellosam.net/contribute">Donation</a>.</p>');
        break;
    } ?>
</div>
<?php } ?>
