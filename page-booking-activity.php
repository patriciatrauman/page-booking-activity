<?php

/**
 * @package page-booking-activity
 * @version 0.1
 */
/**
 * Plugin Name:       Page Booking Activity
 * Plugin URI:        http://faitparpatricia.fr/
 * Description:       This plugin let you create page event and let authorized member to administrate it while register member can book
 * Version:           1.0
 * Requires at least: 5.9
 * Author:            Patricia Trauman
 * Author URI:        http://faitparpatricia.fr/
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 */


if (!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
define('PRA_VERSION', '0.1');
define('PRA_MINIMUM_WP_VERSION', '5.0');
define('PRA_PREFIX_PLUGIN', 'pra');
define('PRA_PLUGIN_DIR', plugin_dir_path(__FILE__));

register_activation_hook(__FILE__, array('Pra', 'plugin_activation'));
register_deactivation_hook(__FILE__, array('Pra', 'plugin_deactivation'));
require_once(PRA_PLUGIN_DIR . 'index.php');
require_once(PRA_PLUGIN_DIR . 'class.pra.php');
require_once(PRA_PLUGIN_DIR . 'class.pra-front.php');
add_action('init', array('Pra', 'init'));
add_action('admin_enqueue_scripts', 'load_general_sources');
// add_action('init', array('Pra_Front', 'init'));
// add_action('admin_enqueue_scripts', array('Pra_Front', 'load_resources'));
function load_general_sources()
{
	wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Material+Icons:ital,wght@0,300;0,400;0,700;1,400&family=Neuton:ital,wght@0,300;0,400;0,700;1,400&display=swap', [], null);
	// wp_register_style(PRA_PREFIX_PLUGIN . '.css', plugin_dir_url(__FILE__) . '_inc/' . PRA_PREFIX_PLUGIN . '.css', array(), PRA_VERSION);
	wp_register_style(PRA_PREFIX_PLUGIN . '.css', plugin_dir_url(__FILE__) . '_inc/' . PRA_PREFIX_PLUGIN . '.css');
	wp_enqueue_style(PRA_PREFIX_PLUGIN . '.css');
}

if (is_admin()) {
	require_once(PRA_PLUGIN_DIR . 'class.' . PRA_PREFIX_PLUGIN . '-admin.php');
	add_action('init', array('Pra_Admin', 'init'));
}
