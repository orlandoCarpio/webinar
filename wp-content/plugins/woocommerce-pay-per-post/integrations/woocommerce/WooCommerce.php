<?php

namespace PRAMADILLO\INTEGRATIONS;

use  Woocommerce_Pay_Per_Post_Helper ;
class WooCommerce
{
    private  $available_templates ;
    public function __construct()
    {
        $this->available_templates = array(
            'woocommerce-order-receipt' => 'woocommerce-order-receipt.php',
        );
    }
    
    /**
     * @param $formatted_meta
     * @param $item
     *
     * @return string
     */
    public function get_purchased_content_by_product_id( $formatted_meta, $item )
    {
        
        if ( $item->get_variation_id() > 0 ) {
            $purchased = Woocommerce_Pay_Per_Post_Helper::get_posts_associated_with_product_id( $item->get_variation_id() );
        } else {
            $purchased = Woocommerce_Pay_Per_Post_Helper::get_posts_associated_with_product_id( $item->get_product_id() );
        }
        
        $template = 'woocommerce-order-receipt';
        
        if ( !empty($purchased) ) {
            ob_start();
            $template_file = ( locate_template( WC_PPP_TEMPLATE_PATH . $this->available_templates[$template] ) ?: WC_PPP_PATH . 'public/partials/' . $this->available_templates[$template] );
            require $template_file;
            return ob_get_clean();
        }
        
        return $formatted_meta;
    }
    
    public function hide_item_meta_from_email( $css, $email )
    {
        return $css . '.ppp-purchased-content {display:none;}';
    }

}