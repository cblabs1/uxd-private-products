<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin settings page — set the global private-product password.
 */
class UXD_PP_Admin_Settings {

    const OPTION_KEY = 'uxd_pp_password';

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function add_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Private Products', 'uxd-private-products' ),
            __( 'Private Products', 'uxd-private-products' ),
            'manage_woocommerce',
            'uxd-private-products',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings() {
        register_setting(
            'uxd_pp_settings_group',
            self::OPTION_KEY,
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );

        add_settings_section(
            'uxd_pp_main_section',
            __( 'Access Password', 'uxd-private-products' ),
            '__return_false',
            'uxd-private-products'
        );

        add_settings_field(
            self::OPTION_KEY,
            __( 'Password', 'uxd-private-products' ),
            [ __CLASS__, 'render_password_field' ],
            'uxd-private-products',
            'uxd_pp_main_section'
        );
    }

    public static function render_password_field() {
        $value = get_option( self::OPTION_KEY, '' );
        ?>
        <input
            type="text"
            name="<?php echo esc_attr( self::OPTION_KEY ); ?>"
            id="<?php echo esc_attr( self::OPTION_KEY ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
            autocomplete="off"
        />
        <p class="description">
            <?php esc_html_e( 'Customers must enter this password to view any product marked as Private.', 'uxd-private-products' ); ?>
        </p>
        <?php
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $password_missing = empty( trim( get_option( self::OPTION_KEY, '' ) ) );
        ?>
        <div class="wrap uxd-pp-settings">
            <h1><?php esc_html_e( 'Private Products Settings', 'uxd-private-products' ); ?></h1>

            <?php if ( $password_missing ) : ?>
            <div class="notice notice-error" style="margin:0 0 1.5rem;">
                <p>
                    <strong><?php esc_html_e( '⚠ No password is set.', 'uxd-private-products' ); ?></strong>
                    <?php esc_html_e( 'All private products are currently HIDDEN from visitors but cannot be unlocked. Please set a password below and save.', 'uxd-private-products' ); ?>
                </p>
            </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'uxd_pp_settings_group' );
                do_settings_sections( 'uxd-private-products' );
                submit_button( __( 'Save Password', 'uxd-private-products' ) );
                ?>
            </form>

            <hr />
            <h2><?php esc_html_e( 'How it works', 'uxd-private-products' ); ?></h2>
            <ul style="list-style:disc;padding-left:1.5em;">
                <li><?php esc_html_e( 'Set a password above.', 'uxd-private-products' ); ?></li>
                <li><?php esc_html_e( 'Edit any product and enable the "Private Product" option in the Product Data panel.', 'uxd-private-products' ); ?></li>
                <li><?php esc_html_e( 'Private products are hidden from shop and category pages until the correct password is entered.', 'uxd-private-products' ); ?></li>
                <li><?php esc_html_e( 'Visiting the single product page also shows a password prompt.', 'uxd-private-products' ); ?></li>
                <li><?php esc_html_e( 'Access is remembered for the browser session via a secure cookie.', 'uxd-private-products' ); ?></li>
            </ul>
        </div>
        <?php
    }

    /**
     * Return the currently saved password.
     */
    public static function get_password() {
        return get_option( self::OPTION_KEY, '' );
    }
}
