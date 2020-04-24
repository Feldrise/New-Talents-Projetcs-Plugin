<?php
/*
Plugin Name: New Talents Projects Manager
Plugin URI: https://new-talents.fr
Description: A plugin to post project on New Talents
Version: 1.0
Author: Feldrise
Author URI: https://feldrise.com
*/

// We include simple class files
include( plugin_dir_path( __FILE__ ) . 'webhooks.php');
include( plugin_dir_path( __FILE__ ) . 'class.channel.php');

// We include widgets
include( plugin_dir_path( __FILE__ ) . 'class.widgets.project-form.php');

// We init the plugin
add_action( 'init', array( 'WidgetProjectForm', 'init' ) );
?>