<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frequently Bought Together Widget
 */
class FBT_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'frequently-bought-together';
    }

    public function get_title() {
        return __('よく一緒に購入されている商品', 'frequently-bought-together');
    }

    public function get_icon() {
        return 'eicon-products';
    }

    public function get_categories() {
        return ['woocommerce-elements'];
    }

    public function get_keywords() {
        return ['woocommerce', 'shop', 'store', 'product', 'frequently', 'bought', 'together', 'bundle'];
    }

    protected function register_controls() {
        // コンテンツセクション
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('コンテンツ', 'frequently-bought-together'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'section_title',
            [
                'label' => __('セクションタイトル', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('よく一緒に購入されている商品', 'frequently-bought-together'),
                'placeholder' => __('タイトルを入力', 'frequently-bought-together'),
            ]
        );

        $this->add_control(
            'selected_products',
            [
                'label' => __('商品を選択', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_products_options(),
                'label_block' => true,
                'description' => __('表示する商品を選択してください（最大6個推奨）', 'frequently-bought-together'),
            ]
        );

        $this->add_control(
            'loop_template',
            [
                'label' => __('ループテンプレート', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_loop_templates(),
                'default' => '',
                'description' => __('商品表示に使用するElementorループテンプレートを選択', 'frequently-bought-together'),
            ]
        );

        $this->add_control(
            'show_price',
            [
                'label' => __('価格を表示', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('表示', 'frequently-bought-together'),
                'label_off' => __('非表示', 'frequently-bought-together'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_total',
            [
                'label' => __('合計価格を表示', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('表示', 'frequently-bought-together'),
                'label_off' => __('非表示', 'frequently-bought-together'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('ボタンテキスト', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('選択した商品をカートに追加', 'frequently-bought-together'),
            ]
        );

        $this->end_controls_section();

        // スタイルセクション - レイアウト
        $this->start_controls_section(
            'layout_style_section',
            [
                'label' => __('レイアウト', 'frequently-bought-together'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('カラム数', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .fbt-products-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_responsive_control(
            'column_gap',
            [
                'label' => __('カラム間隔', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 40,
                ],
                'selectors' => [
                    '{{WRAPPER}} .fbt-products-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // スタイルセクション - タイトル
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => __('タイトル', 'frequently-bought-together'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .fbt-section-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('テキストカラー', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fbt-section-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('マージン', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fbt-section-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // スタイルセクション - 商品カード
        $this->start_controls_section(
            'card_style_section',
            [
                'label' => __('商品カード', 'frequently-bought-together'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_background',
            [
                'label' => __('背景色', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .fbt-product-item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'selector' => '{{WRAPPER}} .fbt-product-item',
            ]
        );

        $this->add_responsive_control(
            'card_border_radius',
            [
                'label' => __('角丸', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fbt-product-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .fbt-product-item',
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('パディング', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fbt-product-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // スタイルセクション - ボタン
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('ボタン', 'frequently-bought-together'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .fbt-add-to-cart-btn',
            ]
        );

        $this->start_controls_tabs('button_style_tabs');

        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => __('通常', 'frequently-bought-together'),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('テキストカラー', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .fbt-add-to-cart-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('背景色', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0071e3',
                'selectors' => [
                    '{{WRAPPER}} .fbt-add-to-cart-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_hover_tab',
            [
                'label' => __('ホバー', 'frequently-bought-together'),
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label' => __('テキストカラー', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fbt-add-to-cart-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background_color',
            [
                'label' => __('背景色', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fbt-add-to-cart-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('パディング', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .fbt-add-to-cart-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => __('角丸', 'frequently-bought-together'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .fbt-add-to-cart-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get products for select2 (including variations)
     */
    protected function get_products_options() {
        $products = wc_get_products([
            'limit' => -1,
            'status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $options = [];
        foreach ($products as $product) {
            $options[$product->get_id()] = $product->get_name() . ' (#' . $product->get_id() . ')';
            
            // Add variations if this is a variable product
            if ($product->is_type('variable')) {
                $variations = $product->get_available_variations();
                foreach ($variations as $variation) {
                    $variation_obj = wc_get_product($variation['variation_id']);
                    if ($variation_obj) {
                        $variation_name = $product->get_name() . ' - ' . implode(', ', $variation_obj->get_variation_attributes());
                        $options[$variation['variation_id']] = $variation_name . ' (#' . $variation['variation_id'] . ')';
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Format variation product name with attribute labels (not slugs)
     */
    protected function get_formatted_variation_name($variation, $parent_product) {
        if (!$variation || !$variation->is_type('variation')) {
            return $variation ? $variation->get_name() : '';
        }
        
        $parent_name = $parent_product->get_name();
        $formatted_attributes = [];
        
        // Get variation attributes
        $attributes = $variation->get_variation_attributes();
        
        foreach ($attributes as $attr_name => $attr_value) {
            $taxonomy = str_replace('attribute_', '', $attr_name);
            
            if (taxonomy_exists($taxonomy)) {
                $term = get_term_by('slug', $attr_value, $taxonomy);
                if ($term) {
                    $formatted_attributes[] = $term->name;
                } else {
                    $formatted_attributes[] = $attr_value;
                }
            } else {
                // For custom attributes, get options from parent product
                $product_attributes = $parent_product->get_attributes();
                $attribute_name = str_replace('attribute_', '', $attr_name);
                
                $display_value = $attr_value;
                if (isset($product_attributes[$attribute_name])) {
                    $attribute_obj = $product_attributes[$attribute_name];
                    $options = $attribute_obj->get_options();
                    // Find matching option
                    foreach ($options as $option) {
                        if (sanitize_title($option) === $attr_value) {
                            $display_value = $option;
                            break;
                        }
                    }
                }
                $formatted_attributes[] = $display_value;
            }
        }
        
        if (!empty($formatted_attributes)) {
            return $parent_name . ' - ' . implode(', ', $formatted_attributes);
        }
        
        return $parent_name;
    }

    /**
     * Get Elementor loop templates
     */
    protected function get_loop_templates() {
        $templates = get_posts([
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_elementor_template_type',
                    'value' => 'loop',
                ],
            ],
        ]);

        $options = ['' => __('デフォルト', 'frequently-bought-together')];
        
        foreach ($templates as $template) {
            $options[$template->ID] = $template->post_title;
        }

        return $options;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $selected_products = $settings['selected_products'];
        
        // Get current product if we're on a product page
        $current_product_id = get_the_ID();
        $current_product = null;
        if (is_product() && $current_product_id) {
            $current_product = wc_get_product($current_product_id);
        }
        
        // Add current product as first item if not already in the list
        if ($current_product && !in_array($current_product_id, (array)$selected_products)) {
            array_unshift($selected_products, $current_product_id);
        }

        if (empty($selected_products)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div style="padding: 20px; text-align: center; background: #f0f0f0; border: 2px dashed #ccc;">';
                echo __('商品を選択してください（商品ページでは自動的に現在の商品が追加されます）', 'frequently-bought-together');
                echo '</div>';
            }
            return;
        }

        $products = [];
        $product_display_names = []; // Store formatted names
        $total_price = 0;
        $total_price_incl_tax = 0;
        $first_product_is_variable = false;
        $first_product_variations = [];
        $initial_variation_id = null;
        $parent_products = []; // Store parent products for variation name formatting

        foreach ($selected_products as $index => $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                // Check if first product is variable and get variations
                if ($index === 0 && $product->is_type('variable')) {
                    $first_product_is_variable = true;
                    $available_variations = $product->get_available_variations();
                    
                    // Find initial variation: first in-stock or first available
                    $has_out_of_stock = false;
                    foreach ($available_variations as $variation_data) {
                        $variation = wc_get_product($variation_data['variation_id']);
                        if ($variation) {
                            // Get variation attributes with labels (not slugs)
                            $formatted_attributes = [];
                            
                            if (!empty($variation_data['attributes'])) {
                                foreach ($variation_data['attributes'] as $attr_name => $attr_value) {
                                    // Get the taxonomy label
                                    $taxonomy = str_replace('attribute_', '', $attr_name);
                                    if (taxonomy_exists($taxonomy)) {
                                        $term = get_term_by('slug', $attr_value, $taxonomy);
                                        if ($term) {
                                            $formatted_attributes[] = $term->name;
                                        } else {
                                            $formatted_attributes[] = $attr_value;
                                        }
                                    } else {
                                        // For custom attributes, get options from parent product
                                        $product_attributes = $product->get_attributes();
                                        $attribute_name = str_replace('attribute_', '', $attr_name);
                                        
                                        $display_value = $attr_value;
                                        if (isset($product_attributes[$attribute_name])) {
                                            $attribute_obj = $product_attributes[$attribute_name];
                                            $options = $attribute_obj->get_options();
                                            // Find matching option (case-insensitive)
                                            foreach ($options as $option) {
                                                if (sanitize_title($option) === $attr_value) {
                                                    $display_value = $option;
                                                    break;
                                                }
                                            }
                                        }
                                        $formatted_attributes[] = $display_value;
                                    }
                                }
                            }
                            
                            $first_product_variations[] = [
                                'id' => $variation->get_id(),
                                'name' => implode(', ', $formatted_attributes),
                                'price' => $variation->get_price(),
                                'price_incl_tax' => wc_get_price_including_tax($variation),
                                'in_stock' => $variation->is_in_stock()
                            ];
                            
                            if (!$variation->is_in_stock()) {
                                $has_out_of_stock = true;
                            }
                            
                            // Set initial variation
                            if ($initial_variation_id === null) {
                                if ($has_out_of_stock) {
                                    // If there are out-of-stock items, select first in-stock
                                    if ($variation->is_in_stock()) {
                                        $initial_variation_id = $variation->get_id();
                                    }
                                } else {
                                    // All in stock, select first one
                                    $initial_variation_id = $variation->get_id();
                                }
                            }
                        }
                    }
                    
                    // If no in-stock variation found, use first variation
                    if ($initial_variation_id === null && !empty($first_product_variations)) {
                        $initial_variation_id = $first_product_variations[0]['id'];
                    }
                    
                    // Use initial variation for calculation
                    if ($initial_variation_id) {
                        $initial_variation = wc_get_product($initial_variation_id);
                        $products[] = $initial_variation;
                        $parent_products[] = $product; // Store parent for name formatting
                        $product_display_names[] = $this->get_formatted_variation_name($initial_variation, $product);
                        $price = (float) $initial_variation->get_price();
                        $total_price += $price;
                        $total_price_incl_tax += (float) wc_get_price_including_tax($initial_variation);
                    }
                } else {
                    $products[] = $product;
                    $parent_products[] = null; // Not a variation
                    $product_display_names[] = $product->get_name();
                    $price = (float) $product->get_price();
                    $total_price += $price;
                    // Calculate price including tax
                    $total_price_incl_tax += (float) wc_get_price_including_tax($product);
                }
            }
        }

        if (empty($products)) {
            return;
        }
        
        // Check if points plugin is active
        $has_points = class_exists('WC_Points_Rewards') || function_exists('wc_points_rewards_get_points_label');

        ?>
        <div class="fbt-widget-wrapper">
            <?php if (!empty($settings['section_title'])) : ?>
                <h3 class="fbt-section-title"><?php echo esc_html($settings['section_title']); ?></h3>
            <?php endif; ?>

            <div class="fbt-products-grid">
                <?php foreach ($products as $index => $product) : 
                    $is_first_variable = ($index === 0 && $first_product_is_variable);
                    $current_product_id = $is_first_variable ? $initial_variation_id : $product->get_id();
                ?>
                    <div class="fbt-product-item" data-product-id="<?php echo esc_attr($current_product_id); ?>">
                        <div class="fbt-product-checkbox-wrapper">
                            <input type="checkbox" 
                                   class="fbt-product-checkbox" 
                                   checked 
                                   data-product-id="<?php echo esc_attr($current_product_id); ?>"
                                   data-price="<?php echo esc_attr($product->get_price()); ?>"
                                   data-price-incl-tax="<?php echo esc_attr(wc_get_price_including_tax($product)); ?>">
                        </div>
                        
                        <?php if (!empty($settings['loop_template'])) : ?>
                            <?php
                            // Use Elementor loop template
                            echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($settings['loop_template']);
                            ?>
                        <?php else : ?>
                            <!-- デフォルトテンプレート -->
                            <div class="fbt-product-content">
                                <div class="fbt-product-image">
                                    <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                        <?php echo $product->get_image('medium'); ?>
                                    </a>
                                </div>
                                <div class="fbt-product-info">
                                    <h4 class="fbt-product-title">
                                        <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                            <?php echo esc_html($product_display_names[$index]); ?>
                                        </a>
                                    </h4>
                                    <?php if ($is_first_variable && !empty($first_product_variations)) : ?>
                                    <div class="fbt-variation-selector">
                                        <select class="fbt-variation-select" data-parent-product-id="<?php echo esc_attr($selected_products[0]); ?>">
                                            <?php foreach ($first_product_variations as $var) : ?>
                                                <option value="<?php echo esc_attr($var['id']); ?>"
                                                        data-price="<?php echo esc_attr($var['price']); ?>"
                                                        data-price-incl-tax="<?php echo esc_attr($var['price_incl_tax']); ?>"
                                                        <?php echo ($var['id'] == $initial_variation_id) ? 'selected' : ''; ?>
                                                        <?php echo !$var['in_stock'] ? 'disabled' : ''; ?>>
                                                    <?php echo esc_html($var['name']); ?>
                                                    <?php echo !$var['in_stock'] ? ' (在庫切れ)' : ''; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($settings['show_price'] === 'yes') : ?>
                                        <div class="fbt-product-price">
                                            <?php 
                                            // Display price including tax
                                            $price_incl_tax = wc_get_price_including_tax($product);
                                            echo wc_price($price_incl_tax);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="fbt-summary">
                <?php if ($settings['show_total'] === 'yes') : ?>
                    <div class="fbt-total-price">
                        <span class="fbt-total-label"><?php echo __('合計:', 'frequently-bought-together'); ?></span>
                        <span class="fbt-total-amount" 
                              data-total="<?php echo esc_attr($total_price); ?>"
                              data-total-incl-tax="<?php echo esc_attr($total_price_incl_tax); ?>">
                            <?php echo wc_price($total_price_incl_tax); ?>
                        </span>
                        <?php if ($has_points) : ?>
                            <span class="fbt-point-info">
                                <?php 
                                $points = floor($total_price_incl_tax * 0.01); // 1%ポイント
                                printf(__('ポイントの合計: %dpt', 'frequently-bought-together'), $points);
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <button type="button" class="fbt-add-to-cart-btn">
                    <?php echo esc_html($settings['button_text']); ?>
                </button>
            </div>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        if ( settings.selected_products.length === 0 ) {
            #>
            <div style="padding: 20px; text-align: center; background: #f0f0f0; border: 2px dashed #ccc;">
                商品を選択してください
            </div>
            <#
            return;
        }
        #>

        <div class="fbt-widget-wrapper">
            <# if ( settings.section_title ) { #>
                <h3 class="fbt-section-title">{{{ settings.section_title }}}</h3>
            <# } #>

            <div class="fbt-products-grid">
                <div class="fbt-product-item">
                    <div class="fbt-product-checkbox-wrapper">
                        <input type="checkbox" class="fbt-product-checkbox" checked>
                    </div>
                    <div class="fbt-product-content">
                        <div class="fbt-product-image">
                            <div style="background: #f0f0f0; height: 200px; display: flex; align-items: center; justify-content: center;">
                                商品画像
                            </div>
                        </div>
                        <div class="fbt-product-info">
                            <h4 class="fbt-product-title">商品名</h4>
                            <# if ( settings.show_price === 'yes' ) { #>
                                <div class="fbt-product-price">¥1,000</div>
                            <# } #>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fbt-summary">
                <# if ( settings.show_total === 'yes' ) { #>
                    <div class="fbt-total-price">
                        <span class="fbt-total-label">合計:</span>
                        <span class="fbt-total-amount">¥3,000</span>
                        <span class="fbt-point-info">ポイントの合計: 30pt</span>
                    </div>
                <# } #>

                <button type="button" class="fbt-add-to-cart-btn">
                    {{{ settings.button_text }}}
                </button>
            </div>
        </div>
        <?php
    }
}
