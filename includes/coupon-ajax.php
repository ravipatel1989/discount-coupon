<?php
class couponAjaxCls{
    public function __construct() {
        $this->init();
    }
    
    public function init(){
        add_action('wp_ajax_data_fetch' , array($this,'data_fetch'));
        add_action('wp_ajax_nopriv_data_fetch',array($this,'data_fetch'));
    }

    // show lsit of product on entered keyword
    public function data_fetch(){
            global $wpdb;
            $query = $_POST['keyword'];
            $query = trim($query);
            if($query == ""){
                die;
            }
            $products = $wpdb->get_results( $wpdb->prepare( "SELECT wp.ID, wp.post_title FROM `{$wpdb->prefix}posts` AS wp LEFT JOIN `{$wpdb->prefix}postmeta` as wpm ON wp.ID = wpm.post_id WHERE wp.`post_type` LIKE 'product' AND wp.`post_status` LIKE 'publish' AND wpm.`meta_key` LIKE '_sku' AND (wp.post_title LIKE '%$query%' OR wpm.`meta_value` LIKE '%$query%')") );
            if(!empty($products)){
                echo '<ul id="searched_products">';
                foreach ($products as $product) {
                    $_product = wc_get_product( $product->ID );
                    if($_product->is_type( 'variable' )){
                        $variations = $_product->get_available_variations();
                        foreach($variations as $variation){
                            $variationId = $variation['variation_id'];
                            $variation_product = wc_get_product($variationId);
                            $sku = $variation['sku'];
                            $price = $variation_product->get_regular_price();
                            $sale_price = $variation_product->get_sale_price();
                        ?>
                        <li id="<?php echo $variationId; ?>" data-title="<?php echo $product->post_title; ?>" data-sku="<?php echo $sku; ?>" data-price="<?php echo $price; ?>" data-sale_price="<?php echo $sale_price; ?>"><a href="#" id="<?php echo $variationId; ?>"><?php echo $product->post_title.'-'.$sku; ?></a></li>
                        <?php
                        }
                    }else{
                        $sku = $_product->get_sku();
                        $price = $_product->get_regular_price() ? $_product->get_regular_price() : $_product->get_price();
                        $sale_price = $_product->get_sale_price();
                    ?>
                    <li id="<?php echo $product->ID; ?>" data-title="<?php echo $product->post_title; ?>" data-sku="<?php echo $sku; ?>" data-price="<?php echo $price; ?>" data-sale_price="<?php echo $sale_price; ?>"><a href="#" id="<?php echo $product->ID; ?>"><?php echo $product->post_title; ?></a></li>
    
                    <?php
                    }
                ?>
                <?php
                }
                echo '</ul>';
            }
            die();
    }
}

$couponAjaxObj = new couponAjaxCls();