<?php
defined( 'ABSPATH' ) || exit;

/**
 * Adds a "Private Product" checkbox to the WooCommerce product data panel.
 */
class UXD_PP_Product_Options {

    const META_KEY = '_uxd_pp_is_private';

    public static function init() {
        // General tab — add the checkbox field.
        add_action( 'woocommerce_product_options_general_product_data', [ __CLASS__, 'add_field' ] );

        // Save the field value.
        add_action( 'woocommerce_process_product_meta', [ __CLASS__, 'save_field' ] );

        // Show a badge in the product list table.
        add_filter( 'manage_product_posts_columns', [ __CLASS__, 'add_column' ] );
        add_action( 'manage_product_posts_custom_column', [ __CLASS__, 'render_column' ], 10, 2 );
    }

    public static function add_field() {
        echo '<div class="options_group">';
        woocommerce_wp_checkbox( [
            'id'          => self::META_KEY,
            'label'       => __( 'Private Product', 'uxd-private-products' ),
            'description' => __( 'When enabled, this product is hidden until the visitor enters the correct password.', 'uxd-private-products' ),
        ] );
        echo '</div>';
    }

    public static function save_field( $post_id ) {
        $value = isset( $_POST[ self::META_KEY ] ) ? 'yes' : 'no';
        update_post_meta( $post_id, self::META_KEY, $value );
    }

    /**
     * Returns true if the given product (by ID) is marked private.
     */
    public static function is_private( $product_id ) {
        return 'yes' === get_post_meta( $product_id, self::META_KEY, true );
    }

    /* ── Admin list column ─────────────────────────────────────────── */

    public static function add_column( $columns ) {
        $new = [];
        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;
            if ( 'name' === $key ) {
                $new['uxd_pp_private'] = __( 'Private', 'uxd-private-products' );
            }
        }
        return $new;
    }

    public static function render_column( $column, $post_id ) {
        if ( 'uxd_pp_private' !== $column ) {
            return;
        }
        if ( self::is_private( $post_id ) ) {
            echo '<span title="' . esc_attr__( 'Private Product', 'uxd-private-products' ) . '" style="color:#d63638;font-size:18px;">&#128274;</span>';
        } else {
            echo '&mdash;';
        }
    }
}
