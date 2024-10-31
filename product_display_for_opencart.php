<?php  
/*
Plugin Name: Product Display for OpenCart
Plugin URI:  https://www.thatsoftwareguy.com/wp_opencart_product_display.html
Description: Shows off a product from your OpenCart based store on your blog.
Version:     1.0
Author:      That Software Guy 
Author URI:  https://www.thatsoftwareguy.com 
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: oc_product_display
Domain Path: /languages
*/

function oc_product_display_shortcode($atts = [], $content = null, $tag = '')
{
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $id =  $atts['id']; 

    $ocpd_settings = get_option('ocpd_settings'); 
    $url = $ocpd_settings['ocpd_url'];
    $result = file_get_contents($url . "/index.php?route=extension/product/wp_product_display&product_id=".$id); 
    $data = json_decode($result, true); 

    // Escape data 
    $data['name'] = wp_kses_post($data['name']); 
    $data['price'] = wp_kses_post($data['price']); 
    $data['special'] = wp_kses_post($data['special']); 
    $data['link'] = esc_url($data['link']); 
    $data['image'] = wp_kses_post($data['image']); 
    $data['description'] = wp_kses_post($data['description']); 

    // start output
    $o = '';
 
    // start box
    $o .= '<div class="oc_product_display-box">';
 
    $o .= '<div id="prod-left">' . '<a href="' . $data['link'] . '">' . $data['image'] . '</a>' . '</div>'; 
    $o .= '<div id="prod-right">' . '<a href="' .  $data['link'] . '">' . $data['name'] . '</a>' . '<br />';
    if (!$data['special']) { 
       $o .= $data['price']; 
    } else {
       $o .= '<span style="text-decoration: line-through;">' .  $data['price'] . '</span>' . '&nbsp;' . $data['special']; 
    }
    $o .= '</div>';  
    $o .= '<div class="prod-clear"></div>'; 
    $o .= '<div id="prod-desc">' . $data['description'] . '</div>';  

    // enclosing tags
    if (!is_null($content)) {
        // secure output by executing the_content filter hook on $content
        $o .= apply_filters('the_content', $content);
 
        // run shortcode parser recursively
        $o .= do_shortcode($content);
    }
 
    // end box
    $o .= '</div>';
 
    // return output
    return $o;
}
 
function oc_product_display_shortcodes_init()
{
    wp_register_style('oc_product_display', plugins_url('style.css',__FILE__ ));
    wp_enqueue_style('oc_product_display');

    add_shortcode('oc_product_display', 'oc_product_display_shortcode');
}
 
add_action('init', 'oc_product_display_shortcodes_init');

add_action( 'admin_menu', 'ocpd_add_admin_menu' );
add_action( 'admin_init', 'ocpd_settings_init' );


function ocpd_add_admin_menu(  ) { 

    add_options_page( 'Product Display for Opencart', 'Product Display for OpenCart', 'manage_options', 'opencart_product_display_', 'ocpd_options_page' );

}


function ocpd_settings_init(  ) { 

    register_setting( 'ocpd_pluginPage', 'ocpd_settings' );

    add_settings_section(
        'ocpd_pluginPage_section', 
        __( 'Settings', 'wordpress' ), 
        'ocpd_settings_section_callback', 
        'ocpd_pluginPage'
    );

    $args = array('size' => '80'); 
    add_settings_field( 
        'ocpd_url', 
        __( 'Your OpenCart URL', 'wordpress' ), 
        'ocpd_url_render', 
        'ocpd_pluginPage', 
        'ocpd_pluginPage_section', 
        $args 
    );


}


function ocpd_url_render($args) { 

    $options = get_option( 'ocpd_settings' );
    ?>
    <input type='text' name='ocpd_settings[ocpd_url]' value='<?php echo $options['ocpd_url']; ?>'
    <?php 
     if (is_array($args) && sizeof($args) > 0) {
        foreach ($args as $key => $value) { 
             echo $key . "=" . $value . " "; 
        }
     }
    ?>>
    <?php

}


function ocpd_settings_section_callback(  ) { 

    echo __( 'Settings required by this plugin', 'wordpress' );

}


function ocpd_options_page(  ) { 

    ?>
    <form action='options.php' method='post'>

        <h2>Product Display for OpenCart</h2>

        <?php
        settings_fields( 'ocpd_pluginPage' );
        do_settings_sections( 'ocpd_pluginPage' );
        submit_button();
        ?>

    </form>
    <?php

}
