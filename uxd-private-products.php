<?php
/**
 * Plugin Name: UXD Private Products
 * Plugin URI:  https://uxd.com
 * Description: Make WooCommerce products private — hidden from all pages until a password is entered.
 * Version:     1.2.5
 * Author:      UXD
 * Author URI:  https://uxd.com
 * Text Domain: uxd-private-products
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 */

defined( 'ABSPATH' ) || exit;

define( 'UXD_PP_VERSION', '1.2.5' );
define( 'UXD_PP_FILE',    __FILE__ );
define( 'UXD_PP_DIR',     plugin_dir_path( __FILE__ ) );
define( 'UXD_PP_URL',     plugin_dir_url( __FILE__ ) );
define( 'UXD_PP_COOKIE',  'uxd_pp_access' );

/**
 * Boot the plugin after WooCommerce is loaded.
 */
add_action( 'plugins_loaded', 'uxd_pp_init' );
function uxd_pp_init() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>'
                . esc_html__( 'UXD Private Products requires WooCommerce to be active.', 'uxd-private-products' )
                . '</p></div>';
        } );
        return;
    }

    require_once UXD_PP_DIR . 'includes/class-admin-settings.php';
    require_once UXD_PP_DIR . 'includes/class-product-options.php';
    require_once UXD_PP_DIR . 'includes/class-frontend.php';

    UXD_PP_Admin_Settings::init();
    UXD_PP_Product_Options::init();
    UXD_PP_Frontend::init();

    // Register the Elementor widget once Elementor is ready.
    add_action( 'elementor/widgets/register', 'uxd_pp_register_elementor_widget' );
}

function uxd_pp_register_elementor_widget( $widgets_manager ) {
    if ( ! did_action( 'elementor/loaded' ) ) {
        return;
    }
    require_once UXD_PP_DIR . 'includes/class-elementor-widget.php';
    $widgets_manager->register( new UXD_PP_Elementor_Widget() );
}
