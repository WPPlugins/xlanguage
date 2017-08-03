<?php
/*
Widget - The widget provided by xLanguage

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General 
Public License as published by the Free Software Foundation, either version 2 of the License, or (at your 
option) any later version.

This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage. See the GNU General Public License for
more details.

For full license details see license.txt
============================================================================================================ */

/**
 * xLanguageWidgetListLangs class
 *
 * @package xLanguage
 * @author Sam Wong
 * @copyright Copyright (C) 2008 Sam Wong
 **/

class xLanguageWidgetListLangs extends WidgetHelloSam
{
    var $title  = '';

    function has_config() { return true; }
    
    function load($config)
    {
        if (isset ($config['title']))
            $this->title = $config['title'];
    }
    
    function display($args)
    {
        extract ($args);

        echo $before_widget;
        if ($this->title)
            echo $before_title.apply_filters('localization', $this->title).$after_title;
            
        xlanguage_list_langs(); 
        echo $after_widget;
    }
    
    function config($config, $pos)
    {
        ?>
        <table>
            <tr>
                <th><?php _e('Title:') ?></th>
                <td><input type="text" name="<?php echo $this->config_name ('title', $pos) ?>" value="<?php echo htmlspecialchars ($config['title']) ?>"/></td>
            </tr>
        </table>
        <?php
    }
    
    function save($data)
    {
        return array ('title' => $data['title']);
    }
}

?>
