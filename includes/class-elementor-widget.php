<?php
defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

/**
 * Elementor widget — displays private products in a responsive grid.
 *
 * - When locked: shows a blurred product grid with a single centered password overlay.
 * - When unlocked: shows the full product grid normally.
 */
class UXD_PP_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'uxd-private-products';
    }

    public function get_title() {
        return esc_html__( 'Private Products', 'uxd-private-products' );
    }

    public function get_icon() {
        return 'eicon-lock-user';
    }

    public function get_categories() {
        return [ 'woocommerce-elements', 'general' ];
    }

    public function get_keywords() {
        return [ 'private', 'product', 'woocommerce', 'password', 'locked' ];
    }

    public function get_style_depends() {
        return [ 'uxd-pp-style' ];
    }

    public function get_script_depends() {
        return [ 'uxd-pp-script' ];
    }

    /* ── Controls ──────────────────────────────────────────────────── */

    protected function register_controls() {

        /* ─ Query ─────────────────────────────────────────────── */
        $this->start_controls_section( 'section_query', [
            'label' => esc_html__( 'Query', 'uxd-private-products' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'posts_per_page', [
            'label'   => esc_html__( 'Products per page', 'uxd-private-products' ),
            'type'    => Controls_Manager::NUMBER,
            'default' => 6,
            'min'     => 1,
            'max'     => 48,
        ] );

        $this->add_control( 'orderby', [
            'label'   => esc_html__( 'Order by', 'uxd-private-products' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'date',
            'options' => [
                'date'       => esc_html__( 'Date', 'uxd-private-products' ),
                'title'      => esc_html__( 'Title', 'uxd-private-products' ),
                'price'      => esc_html__( 'Price', 'uxd-private-products' ),
                'popularity' => esc_html__( 'Popularity', 'uxd-private-products' ),
                'rand'       => esc_html__( 'Random', 'uxd-private-products' ),
            ],
        ] );

        $this->add_control( 'order', [
            'label'   => esc_html__( 'Order', 'uxd-private-products' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'DESC',
            'options' => [
                'DESC' => esc_html__( 'Descending', 'uxd-private-products' ),
                'ASC'  => esc_html__( 'Ascending', 'uxd-private-products' ),
            ],
        ] );

        $this->end_controls_section();

        /* ─ Layout ────────────────────────────────────────────── */
        $this->start_controls_section( 'section_layout', [
            'label' => esc_html__( 'Layout', 'uxd-private-products' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_responsive_control( 'columns', [
            'label'          => esc_html__( 'Columns', 'uxd-private-products' ),
            'type'           => Controls_Manager::SELECT,
            'default'        => '3',
            'tablet_default' => '2',
            'mobile_default' => '1',
            'options'        => [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
            ],
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
            ],
        ] );

        $this->add_responsive_control( 'column_gap', [
            'label'      => esc_html__( 'Gap', 'uxd-private-products' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'rem' ],
            'default'    => [ 'size' => 24, 'unit' => 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
            'selectors'  => [
                '{{WRAPPER}} .uxd-pp-grid' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'show_image', [
            'label'        => esc_html__( 'Show Product Image', 'uxd-private-products' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'uxd-private-products' ),
            'label_off'    => esc_html__( 'No', 'uxd-private-products' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'show_sale_badge', [
            'label'        => esc_html__( 'Show Sale Badge', 'uxd-private-products' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'uxd-private-products' ),
            'label_off'    => esc_html__( 'No', 'uxd-private-products' ),
            'return_value' => 'yes',
            'default'      => 'yes',
            'condition'    => [ 'show_image' => 'yes' ],
        ] );

        $this->add_control( 'show_price', [
            'label'        => esc_html__( 'Show Price', 'uxd-private-products' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'uxd-private-products' ),
            'label_off'    => esc_html__( 'No', 'uxd-private-products' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'show_add_to_cart', [
            'label'        => esc_html__( 'Show Add to Cart', 'uxd-private-products' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'uxd-private-products' ),
            'label_off'    => esc_html__( 'No', 'uxd-private-products' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->end_controls_section();

        /* ─ Password Popup ────────────────────────────────────── */
        $this->start_controls_section( 'section_popup', [
            'label' => esc_html__( 'Password Popup', 'uxd-private-products' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'popup_title', [
            'label'   => esc_html__( 'Popup Title', 'uxd-private-products' ),
            'type'    => Controls_Manager::TEXT,
            'default' => esc_html__( 'Private Products', 'uxd-private-products' ),
        ] );

        $this->add_control( 'popup_subtitle', [
            'label'   => esc_html__( 'Popup Subtitle', 'uxd-private-products' ),
            'type'    => Controls_Manager::TEXTAREA,
            'rows'    => 2,
            'default' => esc_html__( 'Enter the password to unlock and view these products.', 'uxd-private-products' ),
        ] );

        $this->add_control( 'popup_btn_text', [
            'label'   => esc_html__( 'Button Text', 'uxd-private-products' ),
            'type'    => Controls_Manager::TEXT,
            'default' => esc_html__( 'Unlock Products', 'uxd-private-products' ),
        ] );

        $this->end_controls_section();

        /* ─ Card Style ────────────────────────────────────────── */
        $this->start_controls_section( 'section_card_style', [
            'label' => esc_html__( 'Card', 'uxd-private-products' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'card_bg_color', [
            'label'     => esc_html__( 'Background Color', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-card' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'card_border_color', [
            'label'     => esc_html__( 'Border Color', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#2e3f6a',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-card' => 'border-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'card_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'uxd-private-products' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'default'    => [ 'size' => 16, 'unit' => 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'selectors'  => [
                '{{WRAPPER}} .uxd-pp-card' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
            'name'     => 'card_shadow',
            'selector' => '{{WRAPPER}} .uxd-pp-card',
        ] );

        $this->add_responsive_control( 'card_padding', [
            'label'      => esc_html__( 'Card Padding', 'uxd-private-products' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'default'    => [ 'size' => 16, 'unit' => 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'selectors'  => [
                '{{WRAPPER}} .uxd-pp-card' => 'padding: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();

        /* ─ Image Box Style ───────────────────────────────────── */
        $this->start_controls_section( 'section_image_style', [
            'label'     => esc_html__( 'Image Box', 'uxd-private-products' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_image' => 'yes' ],
        ] );

        $this->add_control( 'image_box_bg', [
            'label'     => esc_html__( 'Box Background', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-card__image-box' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'image_box_radius', [
            'label'      => esc_html__( 'Box Border Radius', 'uxd-private-products' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'default'    => [ 'size' => 12, 'unit' => 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'selectors'  => [
                '{{WRAPPER}} .uxd-pp-card__image-box' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_responsive_control( 'image_height', [
            'label'      => esc_html__( 'Image Height', 'uxd-private-products' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'vh' ],
            'default'    => [ 'size' => 260, 'unit' => 'px' ],
            'range'      => [ 'px' => [ 'min' => 80, 'max' => 700 ] ],
            'selectors'  => [
                '{{WRAPPER}} .uxd-pp-card__image-box' => 'height: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'image_fit', [
            'label'     => esc_html__( 'Image Fit', 'uxd-private-products' ),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'contain',
            'options'   => [
                'contain' => 'Contain',
                'cover'   => 'Cover',
                'fill'    => 'Fill',
            ],
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-card__image-box img' => 'object-fit: {{VALUE}};',
            ],
        ] );

        $this->add_responsive_control( 'image_padding', [
            'label'      => esc_html__( 'Image Padding', 'uxd-private-products' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'default'    => [ 'size' => 12, 'unit' => 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'selectors'  => [
                '{{WRAPPER}} .uxd-pp-card__image-box' => 'padding: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();

        /* ─ Sale Badge Style ──────────────────────────────────── */
        $this->start_controls_section( 'section_sale_style', [
            'label'     => esc_html__( 'Sale Badge', 'uxd-private-products' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_sale_badge' => 'yes', 'show_image' => 'yes' ],
        ] );

        $this->add_control( 'sale_badge_text', [
            'label'   => esc_html__( 'Badge Text', 'uxd-private-products' ),
            'type'    => Controls_Manager::TEXT,
            'default' => esc_html__( 'Sale!', 'uxd-private-products' ),
        ] );

        $this->add_control( 'sale_badge_bg', [
            'label'     => esc_html__( 'Background', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#3a9ad9',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-card__sale-badge' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'sale_badge_color', [
            'label'     => esc_html__( 'Text Color', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-card__sale-badge' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'sale_badge_typography',
            'selector' => '{{WRAPPER}} .uxd-pp-card__sale-badge',
        ] );

        $this->end_controls_section();

        /* ─ Title Style ───────────────────────────────────────── */
        $this->start_controls_section( 'section_title_style', [
            'label' => esc_html__( 'Title', 'uxd-private-products' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'title_typography',
            'selector' => '{{WRAPPER}} .uxd-pp-card__title, {{WRAPPER}} .uxd-pp-card__title a',
        ] );

        $this->add_control( 'title_color', [
            'label'     => esc_html__( 'Color', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-card__title'   => 'color: {{VALUE}};',
                '{{WRAPPER}} .uxd-pp-card__title a' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_responsive_control( 'title_spacing', [
            'label'      => esc_html__( 'Bottom Spacing', 'uxd-private-products' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'default'    => [ 'size' => 12, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .uxd-pp-card__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();

        /* ─ Price Style ───────────────────────────────────────── */
        $this->start_controls_section( 'section_price_style', [
            'label'     => esc_html__( 'Price', 'uxd-private-products' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_price' => 'yes' ],
        ] );

        $this->add_control( 'price_color', [
            'label'     => esc_html__( 'Sale Price Color', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#e8a020',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-card__price ins .amount' => 'color: {{VALUE}};',
                '{{WRAPPER}} .uxd-pp-card__price > .amount'   => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'regular_price_color', [
            'label'     => esc_html__( 'Regular Price Color', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#e8a020',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-card__price del .amount' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'sale_price_typography',
            'label'    => esc_html__( 'Sale Price Font', 'uxd-private-products' ),
            'selector' => '{{WRAPPER}} .uxd-pp-card__price ins .amount, {{WRAPPER}} .uxd-pp-card__price > .amount',
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'regular_price_typography',
            'label'    => esc_html__( 'Regular Price Font', 'uxd-private-products' ),
            'selector' => '{{WRAPPER}} .uxd-pp-card__price del .amount',
        ] );

        $this->end_controls_section();

        /* ─ Popup Style ───────────────────────────────────────── */
        $this->start_controls_section( 'section_popup_style', [
            'label' => esc_html__( 'Password Popup', 'uxd-private-products' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'popup_bg', [
            'label'     => esc_html__( 'Popup Background', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-popup' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'popup_title_color', [
            'label'     => esc_html__( 'Title Color', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#1e2d50',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-popup__title' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'popup_text_color', [
            'label'     => esc_html__( 'Subtitle Color', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#555555',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-popup__subtitle' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'overlay_color', [
            'label'     => esc_html__( 'Overlay Color', 'uxd-private-products' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => 'rgba(10,18,40,0.55)',
            'selectors' => [
                '{{WRAPPER}} .uxd-pp-widget-overlay' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_section();
    }

    /* ── Render ────────────────────────────────────────────────────── */

    protected function render() {
        $settings     = $this->get_settings_for_display();
        $has_access = UXD_PP_Frontend::has_access();
        $locked     = ! $has_access;

        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $settings['posts_per_page'] ),
            'order'          => $settings['order'],
            'meta_query'     => [
                [
                    'key'   => '_uxd_pp_is_private',
                    'value' => 'yes',
                ],
            ],
        ];

        switch ( $settings['orderby'] ) {
            case 'price':
                $args['orderby']  = 'meta_value_num';
                $args['meta_key'] = '_price';
                break;
            case 'popularity':
                $args['orderby']  = 'meta_value_num';
                $args['meta_key'] = 'total_sales';
                break;
            default:
                $args['orderby'] = $settings['orderby'];
        }

        UXD_PP_Frontend::start_widget_query();
        $query = new WP_Query( $args );
        UXD_PP_Frontend::end_widget_query();

        if ( ! $query->have_posts() ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p style="text-align:center;padding:2rem;color:#aaa;">'
                    . esc_html__( 'No private products found. Mark a product as Private to see it here.', 'uxd-private-products' )
                    . '</p>';
            }
            return;
        }

        // Collect products.
        $products = [];
        while ( $query->have_posts() ) {
            $query->the_post();
            $product = wc_get_product( get_the_ID() );
            if ( $product ) {
                $products[] = $product;
            }
        }
        wp_reset_postdata();

        $wrap_class = 'uxd-pp-widget-wrap';
        if ( $locked ) {
            $wrap_class .= ' uxd-pp-widget-locked';
        }
        ?>
        <div class="<?php echo esc_attr( $wrap_class ); ?>">

            <?php if ( $locked ) : ?>
            <!-- ── Single password popup ── -->
            <div class="uxd-pp-widget-overlay" aria-hidden="true"></div>

            <div class="uxd-pp-popup" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Enter password', 'uxd-private-products' ); ?>">
                <span class="uxd-pp-popup__icon">&#128274;</span>

                <?php if ( ! empty( $settings['popup_title'] ) ) : ?>
                    <h2 class="uxd-pp-popup__title"><?php echo esc_html( $settings['popup_title'] ); ?></h2>
                <?php endif; ?>

                <?php if ( ! empty( $settings['popup_subtitle'] ) ) : ?>
                    <p class="uxd-pp-popup__subtitle"><?php echo esc_html( $settings['popup_subtitle'] ); ?></p>
                <?php endif; ?>

                <?php if ( isset( $_GET['uxd_pp_error'] ) && '1' === $_GET['uxd_pp_error'] ) : ?>
                    <p class="uxd-pp-gate__error" role="alert">
                        <?php esc_html_e( 'Incorrect password. Please try again.', 'uxd-private-products' ); ?>
                    </p>
                <?php endif; ?>

                <?php
                $redirect_url = remove_query_arg( 'uxd_pp_error', get_permalink() ?: home_url() );
                $btn_label    = $settings['popup_btn_text'] ?: __( 'Unlock Products', 'uxd-private-products' );
                echo UXD_PP_Frontend::password_form_fields(
                    $redirect_url,
                    'uxd-pp-popup__form',
                    'uxd-pp-popup__field',
                    'uxd-pp-popup__btn button alt',
                    $btn_label
                );
                ?>
            </div>
            <?php endif; ?>

            <!-- ── Product grid (blurred when locked) ── -->
            <div class="uxd-pp-grid<?php echo $locked ? ' uxd-pp-grid-blurred' : ''; ?>" aria-hidden="<?php echo $locked ? 'true' : 'false'; ?>">
                <?php foreach ( $products as $product ) : ?>
                    <?php $this->render_card( $product, $settings, $locked ); ?>
                <?php endforeach; ?>
            </div>

        </div><!-- /.uxd-pp-widget-wrap -->
        <?php
    }

    /* ── Single card ───────────────────────────────────────────────── */

    private function render_card( WC_Product $product, array $settings, bool $locked ) {
        $id         = $product->get_id();
        $permalink  = get_permalink( $id );
        $on_sale    = $product->is_on_sale();
        $badge_text = ! empty( $settings['sale_badge_text'] ) ? $settings['sale_badge_text'] : __( 'Sale!', 'uxd-private-products' );
        ?>
        <div class="uxd-pp-card">

            <?php if ( 'yes' === $settings['show_image'] ) : ?>
                <div class="uxd-pp-card__image-wrap">

                    <?php if ( $on_sale && 'yes' === $settings['show_sale_badge'] && ! $locked ) : ?>
                        <span class="uxd-pp-card__sale-badge"><?php echo esc_html( $badge_text ); ?></span>
                    <?php endif; ?>

                    <div class="uxd-pp-card__image-box">
                        <?php if ( $locked ) : ?>
                            <?php echo wp_kses_post( $product->get_image( 'woocommerce_single' ) ); ?>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $permalink ); ?>">
                                <?php echo wp_kses_post( $product->get_image( 'woocommerce_single' ) ); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endif; ?>

            <div class="uxd-pp-card__body">

                <h3 class="uxd-pp-card__title">
                    <?php if ( $locked ) : ?>
                        <?php echo esc_html( $product->get_name() ); ?>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
                    <?php endif; ?>
                </h3>

                <?php if ( 'yes' === $settings['show_price'] ) : ?>
                    <div class="uxd-pp-card__price">
                        <?php echo wp_kses_post( $product->get_price_html() ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( 'yes' === $settings['show_add_to_cart'] && ! $locked ) : ?>
                    <div class="uxd-pp-card__actions">
                        <?php woocommerce_template_loop_add_to_cart( [ 'product' => $product ] ); ?>
                    </div>
                <?php endif; ?>

            </div><!-- /.uxd-pp-card__body -->
        </div><!-- /.uxd-pp-card -->
        <?php
    }
}
