<?php
/*
Plugin Name: Role-Based Dashboard Notices
Plugin URI: https://wpblogr.com
Description: Create notices and display them in the dashboard for specific user roles.
Version: 1.0
Requires at least: 5.2
Requires PHP: 7.2
Author: Toufique Alahi
Author URI: https://wpblogr.com/about
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: role-based-dashboard-notices
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Enqueue dashboard widget styles and scripts
function role_dash_enqueue_dashboard_assets() {
    $plugin_version = '1.0'; // Define your plugin version here

    wp_enqueue_style('rbn-dashboard-styles', plugins_url('assets/css/rbn-dashboard-widget.css', __FILE__), [], $plugin_version);
    wp_enqueue_script('rbn-dashboard-scripts', plugins_url('assets/js/rbn-dashboard-widget.js', __FILE__), ['jquery'], $plugin_version, true);
    wp_localize_script('rbn-dashboard-scripts', 'rbnAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('role_dash_nonce')
    ]);
}
add_action('admin_enqueue_scripts', 'role_dash_enqueue_dashboard_assets');

// Include archive page functionalities
require_once plugin_dir_path(__FILE__) . 'includes/archive-page.php';

// Include dashboard widget related functionalitiess
require_once plugin_dir_path(__FILE__) . 'includes/dashboard-widget.php';

// Include user action related functionalities
require_once plugin_dir_path(__FILE__) . 'includes/user-actions.php';

// Include settings page functionalities
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';

// Include register post type functionalities
require_once plugin_dir_path(__FILE__) . 'includes/register-post-type.php';

// Include meta box functionalities
require_once plugin_dir_path(__FILE__) . 'includes/meta-boxes.php';
