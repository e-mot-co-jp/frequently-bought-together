<?php
/**
 * Plugin Name: Frequently Bought Together for Elementor
 * Plugin URI: https://e-mot.co.jp
 * Description: Elementor用のよく一緒に購入される商品ウィジェット（Amazonスタイル）
 * Version: 1.0.0
 * Author: E-mot
 * Author URI: https://e-mot.co.jp
 * Text Domain: frequently-bought-together
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('FBT_VERSION', '1.0.0');
define('FBT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FBT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Frequently_Bought_Together {
    
    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        // Check if Elementor is installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        // Check if WooCommerce is installed and activated
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_woocommerce']);
            return;
        }

        // Register Elementor widget
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // AJAX handlers for product search
        add_action('wp_ajax_fbt_search_products', [$this, 'ajax_search_products']);
        add_action('wp_ajax_nopriv_fbt_search_products', [$this, 'ajax_search_products']);
        
        // AJAX handlers for add to cart
        add_action('wp_ajax_fbt_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_nopriv_fbt_add_to_cart', [$this, 'ajax_add_to_cart']);
        
        // AJAX handler for price formatting
        add_action('wp_ajax_fbt_format_price', [$this, 'ajax_format_price']);
        add_action('wp_ajax_nopriv_fbt_format_price', [$this, 'ajax_format_price']);
    }

    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'frequently-bought-together'),
            '<strong>' . esc_html__('Frequently Bought Together', 'frequently-bought-together') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'frequently-bought-together') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function admin_notice_missing_woocommerce() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'frequently-bought-together'),
            '<strong>' . esc_html__('Frequently Bought Together', 'frequently-bought-together') . '</strong>',
            '<strong>' . esc_html__('WooCommerce', 'frequently-bought-together') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function register_widgets($widgets_manager) {
        require_once FBT_PLUGIN_DIR . 'includes/widgets/class-fbt-widget.php';
        $widgets_manager->register(new \FBT_Widget());
    }

    public function enqueue_scripts() {
        // Don't load on cart or checkout pages to avoid conflicts
        if (is_cart() || is_checkout()) {
            return;
        }
        
        wp_enqueue_style(
            'fbt-style',
            FBT_PLUGIN_URL . 'assets/css/fbt-style.css',
            [],
            FBT_VERSION
        );

        wp_enqueue_script(
            'fbt-script',
            FBT_PLUGIN_URL . 'assets/js/fbt-script.js',
            ['jquery'],
            FBT_VERSION,
            true
        );

        wp_localize_script('fbt-script', 'fbtAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fbt_nonce'),
            'addingToCart' => __('カートに追加中...', 'frequently-bought-together'),
            'added' => __('カートに追加しました', 'frequently-bought-together'),
            'error' => __('エラーが発生しました', 'frequently-bought-together'),
        ]);
    }

    /**
     * AJAX handler for product search
     */
    public function ajax_search_products() {
        check_ajax_referer('fbt_nonce', 'nonce');

        $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        
        if (empty($search)) {
            wp_send_json_error(['message' => 'Search term is required']);
        }

        $args = [
            'post_type' => 'product',
            'posts_per_page' => 20,
            's' => $search,
            'post_status' => 'publish',
        ];

        $products = get_posts($args);
        $results = [];

        foreach ($products as $product) {
            $product_obj = wc_get_product($product->ID);
            $results[] = [
                'id' => $product->ID,
                'text' => $product->post_title . ' (#' . $product->ID . ')',
                'price' => $product_obj->get_price_html(),
            ];
        }

        wp_send_json_success($results);
    }

    /**
     * AJAX handler for add to cart
     */
    public function ajax_add_to_cart() {
        check_ajax_referer('fbt_nonce', 'nonce');

        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];
        
        if (empty($product_ids)) {
            wp_send_json_error(['message' => '商品が選択されていません']);
        }

        $added_products = [];
        $failed_products = [];

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            
            if (!$product) {
                $failed_products[] = $product_id;
                continue;
            }

            $result = WC()->cart->add_to_cart($product_id, 1);
            
            if ($result) {
                $added_products[] = $product_id;
            } else {
                $failed_products[] = $product_id;
            }
        }

        if (!empty($added_products)) {
            wp_send_json_success([
                'message' => sprintf(__('%d個の商品をカートに追加しました', 'frequently-bought-together'), count($added_products)),
                'cart_url' => wc_get_cart_url(),
                'added' => $added_products,
                'failed' => $failed_products,
            ]);
        } else {
            wp_send_json_error([
                'message' => __('商品をカートに追加できませんでした', 'frequently-bought-together'),
                'failed' => $failed_products,
            ]);
        }
    }
    
    /**
     * AJAX handler for price formatting
     */
    public function ajax_format_price() {
        check_ajax_referer('fbt_nonce', 'nonce');

        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        
        wp_send_json_success([
            'formatted' => wc_price($price),
            'raw' => $price,
        ]);
    }
}

// Initialize the plugin
Frequently_Bought_Together::instance();
