<link rel="stylesheet" href="<?php echo $this->url () ?>/admin.css" type="text/css"/>
<?php 
    global $wp_version;
    if (version_compare('2.5', $wp_version) <= 0)
    {
        wp_enqueue_script('jquery'); 
    }
?>
