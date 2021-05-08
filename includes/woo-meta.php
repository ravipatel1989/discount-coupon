<?php
class discountmetacls{
    public function __construct() {
        $this->init();
    }
    public function init(){
        add_action('woocommerce_product_options_general_product_data', array($this,'woocommerce_product_custom_fields'));
        add_action('woocommerce_variation_options_pricing', array($this,'woocommerce_variation_product_custom_fields'), 10, 3);
    }

    //--------------------------------------------------------------------------
    //-------- add readonly discount price to woocommere simple product --------
    //--------------------------------------------------------------------------
    
    public function woocommerce_product_custom_fields() {
        global $woocommerce, $post;
        $productId = $post->ID;
        echo '<div class="product_custom_field">';
        $discountArr = woodiscountcls::return_discount_value();

        // Custom Product Text Field
        woocommerce_wp_text_input(
                array(
                    'id' => '_discount_price',
                    'placeholder' => 'Product Discount Price',
                    'label' => __('Product Discount Price', 'woocommerce'),
                    'desc_tip' => 'true',
                    'value' => $discountArr[$productId],
                    'custom_attributes' => array('readonly' => 'readonly')
                )
        );
        echo '</div>';
    }
    
    //--------------------------------------------------------------------------
    //------- add readonly discount price to woocommere variable product -------
    //--------------------------------------------------------------------------

    public function woocommerce_variation_product_custom_fields( $loop, $variation_data, $variation ){
        global $woocommerce, $post;
        $productId = $variation->post_parent;
        echo '<div class="product_custom_field">';
        $discountArr = woodiscountcls::return_discount_value();

        // Custom Product Text Field
        woocommerce_wp_text_input(
                array(
                    'id' => '_discount_price',
                    'placeholder' => 'Product Discount Price',
                    'label' => __('Product Discount Price', 'woocommerce'),
                    'desc_tip' => 'true',
                    'value' => $discountArr[$productId],
                    'custom_attributes' => array('readonly' => 'readonly')
                )
        );
        echo '</div>';
    }
}
$discountmetaObj = new discountmetacls();