<?php
/*
Plugin Name: Doneren met Mollie
Description: Receive donations via Mollie
Version: 2.10.10
Author: Wobbie.nl
Author URI: https://wobbie.nl
Text Domain: doneren-met-mollie
*/

if (!defined('ABSPATH')) {
    die('Please do not load this file directly!');
}

// Plugin Version
if (!defined('DMM_VERSION')) {
    define('DMM_VERSION', '2.10.10');
}

// Plugin Folder Path
if (!defined('DMM_PLUGIN_PATH')) {
    define('DMM_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

define('DMM_PLUGIN_BASE', plugin_basename(__FILE__));

global $wpdb;

// Includes
require_once DMM_PLUGIN_PATH . 'includes/config.php';
require_once DMM_PLUGIN_PATH . 'includes/functions.php';
require_once DMM_PLUGIN_PATH . 'includes/mollie-api.php';
require_once DMM_PLUGIN_PATH . 'includes/class-webhook.php';
require_once DMM_PLUGIN_PATH . 'includes/class-start.php';

$dmm_webook = new Dmm_Webhook();
$dmm = new Dmm_Start();

// Admin includes and functions
if (is_admin()) {
    if(!class_exists('WP_List_Table'))
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

    require_once DMM_PLUGIN_PATH . 'includes/class-donations-table.php';
    require_once DMM_PLUGIN_PATH . 'includes/class-donors-table.php';
    require_once DMM_PLUGIN_PATH . 'includes/class-subscriptions-table.php';
    require_once DMM_PLUGIN_PATH . 'includes/class-admin.php';

    $dmm_admin = new Dmm_Admin();
}

// Register hook
register_activation_hook(__FILE__, array($dmm, 'dmm_install_database'));
register_uninstall_hook(__FILE__, 'dmm_uninstall_database');

function dmm_uninstall_database()
{
    delete_option('dmm_plugin_version');
}

// Update database when plugin is updated
if (get_option('dmm_version') != DMM_VERSION)
    $dmm->dmm_install_database();
