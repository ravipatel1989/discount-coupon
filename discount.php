<?php
/**
  Plugin Name: Products Dicount
  Plugin URI: #
  Description: Plugin can add Coupons for products.
  Version: 1.0
  Author: Charlie Solanki
  Author URI: #
 */
if (!defined('pluginpath')) {
    define('pluginpath', plugin_dir_path(__FILE__));
}
class woodiscountcls {
    
    public function __construct() {
        $this->init();
    }
    public function init() {
        $postType = get_post_type();
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_script'));
        add_action('init', array($this, 'create_discount_coupon'));
        add_action('save_post', array($this, 'save_product_discount_fn'), 10, 3);
        if(!is_admin() && $postType!=="discount_coupon"){
            add_filter('woocommerce_product_get_price', array($this, 'discount_product_price'), 99, 2);
            add_filter('woocommerce_product_get_regular_price', array($this, 'discount_product_regular_price'), 99, 2);
            add_filter('woocommerce_product_variation_get_regular_price', array($this,'discount_get_variation_price'), 99, 2 );
            add_filter('woocommerce_product_variation_get_price', array($this,'discount_get_variation_price') , 99, 2 );
            add_filter('woocommerce_variation_prices_price', array($this, 'discount_product_variation_prices'), 99, 3);
            add_filter('woocommerce_variation_prices_regular_price', array($this, 'discount_product_variation_prices'), 99, 3);
        }
    }


    public function enqueue_admin_script() {
        wp_enqueue_style('style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '0.1.0', 'all');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '0.1.0', 'all');
        wp_enqueue_script('jquery');
        wp_enqueue_script('discount-dev-custom', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '', true);
        $discount_localize['admin_url'] = admin_url('admin-ajax.php');
        wp_localize_script('discount-dev-custom', 'backendajax', $discount_localize);
    }
    
    //--------------------------------------------------------------------------
    //-------------- create custom post type discount_coupon -------------------
    //--------------------------------------------------------------------------
    public function create_discount_coupon() {
        register_post_type('discount_coupon',
                array(
                    'labels' => array(
                        'name' => 'Discounts',
                        'singular_name' => 'Discount',
                        'add_new' => 'Add New',
                        'add_new_item' => 'Add New Discount',
                        'edit' => 'Edit',
                        'edit_item' => 'Edit Discount',
                        'new_item' => 'New Discount',
                        'view' => 'View',
                        'view_item' => 'View Discount',
                        'search_items' => 'Search Discounts',
                        'not_found' => 'No Discounts found',
                        'not_found_in_trash' => 'No Discounts found in Trash',
                        'parent' => 'Parent Discount'
                    ),
                    'public' => true,
                    'menu_position' => 15,
                    'supports' => array('title'),
                    'taxonomies' => array(''),
                    'menu_icon' => 'dashicons-tag',
                    'has_archive' => true
                )
        );
    }
    //---------------------------------------------------------------------------------------
    //----- add/update discount price in discount_coupon post when publish or update---------
    //---------------------------------------------------------------------------------------
    
    public function save_product_discount_fn($post_id, $post, $update) {
        if ($post->post_type === 'discount_coupon') {
            $start_date = filter_input(INPUT_POST, 'start_date');
            $end_date = filter_input(INPUT_POST, 'end_date');

            update_post_meta($post_id, 'start_date', $start_date);
            update_post_meta($post_id, 'end_date', $end_date);

            remove_action('save_post', array($this, 'save_product_discount_fn'));
            if (!empty($_POST['discounted_price'])) {
                $discountPost = array(
                    'ID' => $post_id,
                    'post_content' => maybe_serialize($_POST['discounted_price'])
                );
                wp_update_post($discountPost);
            }

            add_action('save_post', array($this, 'save_product_discount_fn'));
        }
    }

    //--------------------------------------------------------------------------
    //-------------return discounted sale price to simple product---------------
    //--------------------------------------------------------------------------
    
    public function discount_product_price($price, $product) {
        $discountArr = self::return_discount_value();
        $price = get_post_meta($product->id, '_sale_price', true);
        if(floatval($price) == 0){
            $price = get_post_meta($product->id, '_price', true);
        }
        $discount = $discountArr[$product->id];
        if(isset($discount) && floatval($discount) > 0){
            return floatval($discount);
        }else{
            return floatval($price);
        }
    }

    //--------------------------------------------------------------------------
    //------------return discounted regular price to simple product-------------
    //--------------------------------------------------------------------------
    
    public function discount_product_regular_price($price, $product) {
        $discountArr = self::return_discount_value();
        $price = get_post_meta($product->id, '_sale_price', true);
        if(floatval($price) == 0){
            $price = get_post_meta($product->id, '_regular_price', true);
        }
        $discount = $discountArr[$product->id];
        if(isset($discount) && floatval($discount) > 0){
            return floatval($discount);
        }else{
            return floatval($price);
        }
    }

    //--------------------------------------------------------------------------
    //--------------- get variation price of variable product ------------------
    //--------------------------------------------------------------------------

    public function discount_get_variation_price( $price, $variation ) {
        $discountArr = self::return_discount_value();
        // Delete product cached price  (if needed)
        wc_delete_product_transients($variation->get_id());
        $variationData = $variation->get_data();
        $variationId = $variationData['id'];
        $price = get_post_meta($variationId, '_sale_price', true);
        if(floatval($price) == 0){
            $price = get_post_meta($variationId, '_regular_price', true);
        }
        $discount = $discountArr[$variationId];
        if(isset($discount) && floatval($discount) > 0){
            return floatval($discount);
        }else{
            return floatval($price);
        }
    }

    //--------------------------------------------------------------------------
    //--------------- change variation price of variable product ---------------
    //--------------------------------------------------------------------------

    public function discount_product_variation_prices($price, $variation, $product) {
        $discountArr = self::return_discount_value();
        $variationId = $variation->get_id();
        $price = get_post_meta($variationId, '_sale_price', true);
        if(floatval($price) == 0){
            $price = get_post_meta($variationId, '_regular_price', true);
        }
        // Delete product cached price  (if needed)
        wc_delete_product_transients($variation->get_id());
        $discount = $discountArr[$variationId];
        if(isset($discount) && floatval($discount) > 0){
            return floatval($discount);
        }else{
            return floatval($price);
        }
    }

    //--------------------------------------------------------------------------
    //-------------- return all product discount amount in array ---------------
    //--------------------------------------------------------------------------
    
    public static function return_discount_value() {
        $args = array(
            'post_type' => 'discount_coupon',
            'orderby' => 'date',
            'order'   => 'ASC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'active',
                    'value' => 'Yes',
                    'compare' => '=',
                ),
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'start_date',
                        'value' => date("Y-m-d"),
                        'compare' => '<=',
                        'type' => 'DATE'
                    ),
                    array(
                        'key' => 'end_date',
                        'value' => date("Y-m-d"),
                        'compare' => '>=',
                        'type' => 'DATE'
                    ),
                ),
            ),
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            $contentArr = array();
            while ($query->have_posts()) {
                $query->the_post();
                $content = get_the_content();
                $content = maybe_unserialize($content);
                foreach ($content as $key => $value) {
                    $contentArr[$key] = $value;
                }
            }
        }
        wp_reset_postdata();
        return $contentArr;
    }

}

$woodiscountObj = new woodiscountcls();

require_once pluginpath . 'includes/meta-box/meta-box.php';
require_once pluginpath . 'includes/coupon-form.php';
require_once pluginpath . 'includes/coupon-ajax.php';
require_once pluginpath . 'includes/woo-meta.php';
