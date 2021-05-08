<?php

//Create Form Fields for discount
class discountMetaBoxCls {

    public function __construct() {
        $this->init();
    }

    public function init() {
        add_filter('rwmb_meta_boxes', array($this, 'discount_coupon_register_meta_boxes'));
        add_action('admin_init', array($this, 'custom_form_field'));
        add_action('admin_init', array($this,'product_search'));
    }

    public function discount_coupon_register_meta_boxes($meta_boxes) {
        $prefix = '';
        $productoptions = array();

        $prod_args = array('post_type' => 'product', 'posts_per_page' => -1);
        $prod_loop = new wp_Query($prod_args);
        while ($prod_loop->have_posts()) : $prod_loop->the_post();
            $prod_name = get_the_title($prod_loop->ID);
            $productoptions[$prod_name] = $prod_name;
        endwhile;

        $meta_boxes[] = [
            'title' => esc_html__('Discount Coupon Form', 'product-discount'),
            'id' => 'untitled',
            'post_types' => ['discount_coupon'],
            'context' => 'normal',
            'fields' => [
                [
                    'type' => 'select',
                    'name' => esc_html__('Active', 'product-discount'),
                    'id' => 'active',
                    'options' => [
                        'Yes' => esc_html__('Yes', 'product-discount'),
                        'No' => esc_html__('No', 'product-discount'),
                    ],
                ],
                [
                    'type' => 'date',
                    'name' => esc_html__('Start Date', 'product-discount'),
                    'id' => 'start_date',
                ],
                [
                    'type' => 'date',
                    'name' => esc_html__('End Date', 'product-discount'),
                    'id' => 'end_date',
                ],
            ],
        ];

        return $meta_boxes;
    }

    public function custom_form_field() {
        add_meta_box('discount_coupon_meta_box',
                'Discount Coupon Details',
                array($this, 'display_discount_coupon_meta_box'),
                'discount_coupon', 'normal', 'low'
        );
    }

    public function display_discount_coupon_meta_box($discount_coupon) {
        global $wpdb, $post;
        $postId = $post->ID;
        $postContent = maybe_unserialize($post->post_content);
        ?>
        <table id="product_details">
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Sales Price</th>
                <th>Discounted Price</th>
                <th></th>
            </tr>
            <?php
            if (!empty($postContent)) {
                foreach ($postContent as $productId => $price) {
                    $product = wc_get_product($productId);
                    $salePrice = $product->get_sale_price();
                    ?>
                    <tr id="<?php echo $productId; ?>">
                        <td><?php echo $product->get_name(); ?></td>
                        <td><?php echo $product->get_sku(); ?></td>
                        <td><?php echo $product->get_regular_price() ? $product->get_regular_price() : $product->get_price(); ?></td>
                        <td><?php
                            if (!empty($salePrice)) {
                                echo $salePrice;
                            } else {
                                echo "-";
                            }
                            ?></td>
                        <td><input type="number" size="80" id="discounted_price<?php echo $x; ?>" prod_id="<?php echo $productId; ?>" name="discounted_price[<?php echo $productId; ?>]" value="<?php echo $price; ?>" /></td>
                        <td><button id="<?php echo $productId; ?>" class="btnDelete"><i class="fa fa-close"></i></button></td>
                    </tr>
                <?php
            }
        }
        ?>
        </table>
        <?php
    }
    public function product_search() {
        add_meta_box('discount_product_search',
                'Discount Product Search',
                array($this,'custom_product_search'),
                'discount_coupon', 'normal', 'low'
        );
    }

    public function custom_product_search($discount_coupon) {
        ?>
        <input type="text" name="keyword" id="keyword" />
        <div id="datafetch"></div>
        <span class="msg"></span>
        <?php
    }
}

$discountMetaBoxObj = new discountMetaBoxCls();