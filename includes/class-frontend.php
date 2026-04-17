<?php
defined( 'ABSPATH' ) || exit;

/**
 * Frontend logic using Native WordPress Password Protection Logic.
 */
class UXD_PP_Frontend {

    private static $widget_query_active = false;

    public static function init() {
        // ── AJAX password checker ──
        add_action( 'wp_ajax_nopriv_uxd_pp_check', [ __CLASS__, 'ajax_check' ] );
        add_action( 'wp_ajax_uxd_pp_check',        [ __CLASS__, 'ajax_check' ] );

        // ── Form handler ──────────
        add_action( 'admin_post_nopriv_uxd_pp_unlock', [ __CLASS__, 'process_password_form' ] );
        add_action( 'admin_post_uxd_pp_unlock',        [ __CLASS__, 'process_password_form' ] );

        // ── Assets ────────────────
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );

        // ── Product hiding ────────
        add_action( 'pre_get_posts',                        [ __CLASS__, 'pre_get_posts_filter' ], 10 );
        add_action( 'woocommerce_product_query',            [ __CLASS__, 'exclude_from_query' ] );
        add_filter( 'woocommerce_shortcode_products_query', [ __CLASS__, 'exclude_from_shortcode' ] );

        // ── Single product gate ───
        add_action( 'woocommerce_before_single_product', [ __CLASS__, 'gate_single_product' ] );
    }

    /* ── Widget query bypass ─────────────────────────────────────────────── */

    public static function start_widget_query() { self::$widget_query_active = true;  }
    public static function end_widget_query()   { self::$widget_query_active = false; }

    /* ── Access helpers ──────────────────────────────────────────────────── */

    public static function has_access() {
        $stored = trim( UXD_PP_Admin_Settings::get_password() );
        if ( empty( $stored ) ) {
            return false;
        }
        
        // Use the native WP postpass prefix to force cache bypass
        $cookie_name = 'wp-postpass_uxd_' . COOKIEHASH;
        $cookie      = $_COOKIE[ $cookie_name ] ?? '';
        
        if ( empty( $cookie ) ) {
            return false;
        }

        // Native WP Password Check Logic
        require_once ABSPATH . WPINC . '/class-phpass.php';
        $hasher = new PasswordHash( 8, true );
        
        // WP core hashes the entered password into the cookie, so we check the 
        // plain-text DB password against the hash stored in the user's cookie.
        return $hasher->CheckPassword( $stored, wp_unslash( $cookie ) );
    }

    private static function grant_access( $plain_password ) {
        // Native WP Password Hash Logic
        require_once ABSPATH . WPINC . '/class-phpass.php';
        $hasher = new PasswordHash( 8, true );
        $hash   = $hasher->HashPassword( trim( $plain_password ) );

        $cookie_name = 'wp-postpass_uxd_' . COOKIEHASH;
        $domain      = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
        $path        = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';

        setcookie(
            $cookie_name,
            $hash,
            0, // Expires when the browser closes
            $path,
            $domain,
            is_ssl(),
            true
        );
        
        $_COOKIE[ $cookie_name ] = $hash;
    }

    /* ── AJAX: check password, set cookie on success, return redirect URL ── */

    public static function ajax_check() {
        $entered  = trim( (string) wp_unslash( $_POST['uxd_pp_password'] ?? '' ) );
        $stored   = trim( UXD_PP_Admin_Settings::get_password() );
        $back_url = isset( $_POST['uxd_pp_redirect'] )
            ? esc_url_raw( wp_unslash( $_POST['uxd_pp_redirect'] ) )
            : '';

        if ( ! empty( $stored ) && $entered === $stored ) {
            self::grant_access( $stored );
            
            if ( empty( $back_url ) || ! wp_validate_redirect( $back_url ) ) {
                $back_url = home_url( '/' );
            }
            
            // Clean URL. The wp-postpass cookie will handle cache bypass automatically.
            $back_url = remove_query_arg( 'uxd_pp_error', $back_url );
            $back_url = add_query_arg( 'unlocked', time(), $back_url ); // time() ensures a completely fresh bypass
            wp_send_json_success( [ 'redirect' => $back_url ] );
            
            wp_send_json_success( [ 'redirect' => $back_url ] );
        }

        wp_send_json_error( [
            'message' => __( 'Incorrect password. Please try again.', 'uxd-private-products' ),
        ] );
    }

    /* ── admin-post.php handler: verify + set cookie + redirect ─────────── */

    public static function process_password_form() {
        $entered  = trim( (string) wp_unslash( $_POST['uxd_pp_password'] ?? '' ) );
        $stored   = trim( UXD_PP_Admin_Settings::get_password() );
        $back_url = isset( $_POST['uxd_pp_redirect'] )
            ? esc_url_raw( wp_unslash( $_POST['uxd_pp_redirect'] ) )
            : '';

        if ( empty( $back_url ) || ! wp_validate_redirect( $back_url ) ) {
            $back_url = wp_get_referer() ?: home_url( '/' );
        }
        $back_url = remove_query_arg( 'uxd_pp_error', $back_url );

        if ( ! empty( $stored ) && $entered === $stored ) {
            self::grant_access( $stored );
            nocache_headers();
            wp_safe_redirect( add_query_arg( 'unlocked', time(), $back_url ) );
            exit;
        }

        wp_safe_redirect( add_query_arg( 'uxd_pp_error', '1', $back_url ) );
        exit;
    }

    /* ── Product filtering ───────────────────────────────────────────────── */

    private static function get_private_ids() {
        static $ids = null;
        if ( null !== $ids ) {
            return $ids;
        }
        global $wpdb;
        $rows = $wpdb->get_col( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
            '_uxd_pp_is_private', 'yes'
        ) );
        $ids = array_map( 'intval', (array) $rows );
        return $ids;
    }

    public static function pre_get_posts_filter( WP_Query $query ) {
        if ( is_admin() || self::$widget_query_active || self::has_access() ) {
            return;
        }
        $post_type = $query->get( 'post_type' );
        $is_product = 'product' === $post_type
            || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) )
            || ( $query->is_main_query() && ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) );
        if ( ! $is_product ) {
            return;
        }
        $ids = self::get_private_ids();
        if ( empty( $ids ) ) {
            return;
        }
        $existing = array_filter( array_map( 'intval', (array) $query->get( 'post__not_in' ) ) );
        $query->set( 'post__not_in', array_unique( array_merge( $existing, $ids ) ) );
    }

    public static function exclude_from_query( WP_Query $query ) {
        if ( self::has_access() ) {
            return;
        }
        $ids = self::get_private_ids();
        if ( empty( $ids ) ) {
            return;
        }
        $existing = array_filter( array_map( 'intval', (array) $query->get( 'post__not_in' ) ) );
        $query->set( 'post__not_in', array_unique( array_merge( $existing, $ids ) ) );
    }

    public static function exclude_from_shortcode( array $args ) {
        if ( self::has_access() ) {
            return $args;
        }
        $ids = self::get_private_ids();
        if ( empty( $ids ) ) {
            return $args;
        }
        $existing = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : [];
        $args['post__not_in'] = array_unique( array_merge( $existing, $ids ) );
        return $args;
    }

    /* ── Single product gate ─────────────────────────────────────────────── */

    public static function gate_single_product() {
        global $product;
        if ( ! $product instanceof WC_Product ) {
            return;
        }
        if ( ! UXD_PP_Product_Options::is_private( $product->get_id() ) || self::has_access() ) {
            return;
        }
        remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images',       20 );
        remove_action( 'woocommerce_single_product_summary',        'woocommerce_template_single_title',      5 );
        remove_action( 'woocommerce_single_product_summary',        'woocommerce_template_single_rating',    10 );
        remove_action( 'woocommerce_single_product_summary',        'woocommerce_template_single_price',     10 );
        remove_action( 'woocommerce_single_product_summary',        'woocommerce_template_single_excerpt',   20 );
        remove_action( 'woocommerce_single_product_summary',        'woocommerce_template_single_add_to_cart', 30 );
        remove_action( 'woocommerce_single_product_summary',        'woocommerce_template_single_meta',      40 );
        remove_action( 'woocommerce_single_product_summary',        'woocommerce_template_single_sharing',   50 );
        remove_action( 'woocommerce_after_single_product_summary',  'woocommerce_output_product_data_tabs',  10 );
        remove_action( 'woocommerce_after_single_product_summary',  'woocommerce_upsell_display',            15 );
        remove_action( 'woocommerce_after_single_product_summary',  'woocommerce_output_related_products',   20 );
        add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'render_password_form' ], 5 );
    }

    /* ── Password form HTML ──────────────────────────────────────────────── */

    public static function render_password_form( $redirect_url = '' ) {
        if ( empty( $redirect_url ) ) {
            $redirect_url = remove_query_arg( 'uxd_pp_error' );
        }
        $has_error = isset( $_GET['uxd_pp_error'] ) && '1' === $_GET['uxd_pp_error'];
        ?>
        <div class="uxd-pp-gate">
            <div class="uxd-pp-gate__inner">
                <span class="uxd-pp-gate__icon">&#128274;</span>
                <h2 class="uxd-pp-gate__title">
                    <?php esc_html_e( 'This product is private', 'uxd-private-products' ); ?>
                </h2>
                <p class="uxd-pp-gate__subtitle">
                    <?php esc_html_e( 'Enter the password to view this product.', 'uxd-private-products' ); ?>
                </p>
                <?php if ( $has_error ) : ?>
                    <p class="uxd-pp-gate__error" role="alert">
                        <?php esc_html_e( 'Incorrect password. Please try again.', 'uxd-private-products' ); ?>
                    </p>
                <?php endif; ?>
                
                <?php 
                echo self::password_form_fields( $redirect_url, 'uxd-pp-gate__form', 'uxd-pp-gate__field', 'uxd-pp-gate__btn button alt', __( 'Unlock Product', 'uxd-private-products' ) ); 
                ?>
            </div>
        </div>
        <?php
    }

    public static function password_form_fields( $redirect_url, $form_class, $field_class, $btn_class, $btn_label ) {
        ob_start();
        ?>
        <form class="<?php echo esc_attr( $form_class ); ?>" method="post"
              action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action"          value="uxd_pp_unlock" />
            <input type="hidden" name="uxd_pp_redirect" value="<?php echo esc_url( $redirect_url ); ?>" />

            <div class="<?php echo esc_attr( $field_class ); ?>">
                <input
                    type="password"
                    name="uxd_pp_password"
                    class="uxd-pp-password-input"
                    placeholder="<?php esc_attr_e( 'Enter password', 'uxd-private-products' ); ?>"
                    required
                    autocomplete="current-password"
                />
            </div>

            <button type="submit" class="<?php echo esc_attr( $btn_class ); ?> uxd-pp-submit-btn">
                <?php echo esc_html( $btn_label ); ?>
            </button>

            <p class="uxd-pp-gate__error uxd-pp-inline-error" style="display:none;" role="alert"></p>
        </form>
        <?php
        return ob_get_clean();
    }

    /* ── Assets ──────────────────────────────────────────────────────────── */

    public static function enqueue_assets() {
        wp_enqueue_style(
            'uxd-pp-style',
            UXD_PP_URL . 'assets/css/style.css',
            [],
            UXD_PP_VERSION
        );
        wp_enqueue_script(
            'uxd-pp-script',
            UXD_PP_URL . 'assets/js/script.js',
            [ 'jquery' ],
            UXD_PP_VERSION,
            true
        );
        wp_localize_script( 'uxd-pp-script', 'uxdPP', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'action'  => 'uxd_pp_check',
            'i18n'    => [
                'loading' => __( 'Checking…', 'uxd-private-products' ),
                'error'   => __( 'Incorrect password. Please try again.', 'uxd-private-products' ),
            ],
        ] );
    }
}