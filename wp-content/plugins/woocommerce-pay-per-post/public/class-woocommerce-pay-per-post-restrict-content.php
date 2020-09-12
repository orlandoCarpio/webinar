<?php

namespace PRAMADILLO;

use  Carbon\Carbon ;
use  PRAMADILLO\INTEGRATIONS\PaidMembershipsPro ;
use  PRAMADILLO\INTEGRATIONS\WooCommerceMemberships ;
use  PRAMADILLO\INTEGRATIONS\WooCommerceSubscriptions ;
use  Woocommerce_Pay_Per_Post_Helper ;
/**
 * Class Woocommerce_Pay_Per_Post_Restrict_Content
 */
class Woocommerce_Pay_Per_Post_Restrict_Content
{
    public  $protection_checks ;
    public  $protection_type ;
    public  $user_post_info ;
    public  $current_user ;
    public  $product_ids ;
    public  $product_count ;
    protected  $post_id ;
    protected  $integrations ;
    protected  $should_track_pageview ;
    protected  $available_templates ;
    public function __construct( $post_id = null )
    {
        $this->should_track_pageview = true;
        //The Post ID is null because we use this class for displaying shortcodes as well as within the loop
        
        if ( is_null( $post_id ) ) {
            $this->post_id = get_the_ID();
        } else {
            $this->post_id = $post_id;
        }
        
        $this->current_user = wp_get_current_user();
        $this->protection_checks = [
            'check_if_logged_in',
            'check_if_protected',
            'check_if_should_show_paywall',
            'check_if_admin_call',
            'check_if_purchased',
            'check_if_admin_user_have_access',
            'check_if_has_access'
        ];
        $this->available_templates = array(
            'expiration-status' => 'expiration-status.php',
            'pageview-status'   => 'pageview-status.php',
        );
        $this->product_ids = (array) get_post_meta( $this->post_id, WC_PPP_SLUG . '_product_ids', true );
        $this->product_count = count( $this->product_ids );
        $this->protection_type = Woocommerce_Pay_Per_Post_Helper::is_protected( $this->post_id );
    }
    
    /**
     * Function used to set to not track page view
     *
     * @param bool $track
     */
    public function set_track_pageview( $track = true )
    {
        $this->should_track_pageview = (bool) $track;
    }
    
    /*
    |--------------------------------------------------------------------------
    | Protection Checks
    |--------------------------------------------------------------------------
    |
    | Each of these functions go along with the $protection_checks
    | We loop through each one and test
    |
    */
    public function check_if_admin_call()
    {
        
        if ( is_admin() || !$this->post_id ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Is an admin call' );
            return true;
        }
        
        Woocommerce_Pay_Per_Post_Helper::logger( 'Is NOT an admin call' );
        return false;
    }
    
    public function check_if_admin_user_have_access()
    {
        $admins_allowed_access = (bool) get_option( WC_PPP_SLUG . '_allow_admins_access_to_protected_posts', false );
        // Check and see if admins are allowed to view protected content.
        
        if ( $admins_allowed_access && is_super_admin() ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Administrators HAVE access to all protected posts via settings' );
            return true;
        }
        
        Woocommerce_Pay_Per_Post_Helper::logger( 'Administrators DO NOT HAVE access to protected posts via settings' );
        return false;
    }
    
    public function check_if_purchased()
    {
        Woocommerce_Pay_Per_Post_Helper::logger( 'check_if_purchased() called, looping through all products associated with page' );
        foreach ( $this->product_ids as $id ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Checking to see if user has purchased product #' . $id );
            
            if ( Woocommerce_Pay_Per_Post_Helper::can_use_woocommerce_subscriptions() ) {
                
                if ( wc_customer_bought_product( $this->current_user->user_email, $this->current_user->ID, trim( $id ) ) && !$this->integrations['woocommerce-subscriptions']->is_subscription_product( $id ) ) {
                    Woocommerce_Pay_Per_Post_Helper::logger( 'WooSubscriptions Enabled and User has purchased product id #' . trim( $id ) . ' that is NOT a subscription product' );
                    return true;
                }
            
            } else {
                
                if ( wc_customer_bought_product( $this->current_user->user_email, $this->current_user->ID, trim( $id ) ) ) {
                    Woocommerce_Pay_Per_Post_Helper::logger( 'User has purchased product id #' . trim( $id ) );
                    return true;
                }
            
            }
        
        }
        Woocommerce_Pay_Per_Post_Helper::logger( 'User has NOT purchased product id #' . trim( $id ) );
        return false;
    }
    
    public function check_if_logged_in()
    {
        $logged_in = is_user_logged_in();
        Woocommerce_Pay_Per_Post_Helper::logger( 'Is the user logged in? - ' . (( $logged_in ? 'true' : 'false' )) );
        return $logged_in;
    }
    
    public function check_if_has_access()
    {
        Woocommerce_Pay_Per_Post_Helper::logger( 'check_if_has_access() has been called.' );
        switch ( $this->protection_type ) {
            case 'standard':
                // Since we already check to see if they purchased the product standard protection returns true all the time.
            // Since we already check to see if they purchased the product standard protection returns true all the time.
            case 'delay':
                // Delay protection is same protection as standard, just difference in when to display pay wall, we already checked to see if they purchased product we return true.
                Woocommerce_Pay_Per_Post_Helper::logger( 'Protection Type is Standard or Delayed' );
                return $this->check_if_purchased();
                break;
            case 'page-view':
                Woocommerce_Pay_Per_Post_Helper::logger( 'Protection Type is Page View Protection' );
                return $this->has_access_page_view_protection__premium_only();
                break;
            case 'expire':
                Woocommerce_Pay_Per_Post_Helper::logger( 'Protection Type is Expiration Protection' );
                return $this->has_access_expiry_protection__premium_only();
                break;
        }
        return true;
    }
    
    public function check_if_is_paid_memberships_pro_member()
    {
        //Is user a Paid Memberships Pro Member?
        
        if ( Woocommerce_Pay_Per_Post_Helper::can_use_paid_membership_pro() ) {
            $is_member = $this->integrations['paid-memberships-pro']->is_member( $this->post_id );
            Woocommerce_Pay_Per_Post_Helper::logger( 'Is the user a Paid Memberships Pro Member? - ' . (( $is_member ? 'true' : 'false' )) );
            return $is_member;
        }
        
        Woocommerce_Pay_Per_Post_Helper::logger( 'User is NOT a Paid Memberships Pro member, as Paid Memberships Pro is not installed.' );
        return false;
    }
    
    public function check_if_is_member()
    {
        //Is user a WooCommerce Memberships Member?
        
        if ( Woocommerce_Pay_Per_Post_Helper::can_use_woocommerce_memberships() ) {
            $is_member = $this->integrations['woocommerce-memberships']->is_member( $this->post_id );
            Woocommerce_Pay_Per_Post_Helper::logger( 'Is the user a WooMemberships Member? - ' . (( $is_member ? 'true' : 'false' )) );
            return $is_member;
        }
        
        Woocommerce_Pay_Per_Post_Helper::logger( 'User is NOT a member, as WooMemberships is not installed.' );
        return false;
    }
    
    public function check_if_is_subscriber()
    {
        //Is user a WooCommerce Subscriptions Subscriber?
        
        if ( Woocommerce_Pay_Per_Post_Helper::can_use_woocommerce_subscriptions() ) {
            $is_subscriber = $this->integrations['woocommerce-subscriptions']->is_subscriber( $this->post_id );
            Woocommerce_Pay_Per_Post_Helper::logger( 'Does the user have a valid subscription? - ' . (( $is_subscriber ? 'true' : 'false' )) );
            return $is_subscriber;
        }
        
        Woocommerce_Pay_Per_Post_Helper::logger( 'User is NOT a subscriber, as WooSubscriptions is not installed.' );
        return false;
    }
    
    public function check_if_protected()
    {
        
        if ( !$this->protection_type ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Page is NOT protected.' );
            return false;
        }
        
        Woocommerce_Pay_Per_Post_Helper::logger( 'Page IS protected.' );
        return true;
    }
    
    public function check_if_post_contains_subscription_products()
    {
        $post_has_subscription_product = $this->integrations['woocommerce-subscriptions']->post_contains_subscription_products( $this->post_id );
        Woocommerce_Pay_Per_Post_Helper::logger( 'Does Post/Page Contain Subscription Products? - ' . (( $post_has_subscription_product ? 'true' : 'false' )) );
        return $post_has_subscription_product;
    }
    
    public function check_if_post_contains_membership_products()
    {
        $post_has_membership_product = $this->integrations['woocommerce-memberships']->post_contains_membership_products( $this->post_id );
        Woocommerce_Pay_Per_Post_Helper::logger( 'Does Post Contain Membership Products? - ' . (( $post_has_membership_product ? 'true' : 'false' )) );
        return $post_has_membership_product;
    }
    
    public function check_if_post_contains_paid_memberships_pro_membership_products()
    {
        $post_has_membership_product = $this->integrations['paid-memberships-pro']->post_contains_membership_products( $this->post_id );
        Woocommerce_Pay_Per_Post_Helper::logger( 'Does Post Contain Paid Membership Pro Membership Products? - ' . (( $post_has_membership_product ? 'true' : 'false' )) );
        return $post_has_membership_product;
    }
    
    public function check_if_should_show_paywall()
    {
        switch ( $this->protection_type ) {
            case 'standard':
            case 'page-view':
            case 'expire':
                return true;
                break;
            case 'delay':
                return $this->enable_delay_protection_paywall__premium_only();
                break;
        }
        return true;
    }
    
    public function can_user_view_content()
    {
        $show_paywall = true;
        foreach ( (array) $this->protection_checks as $check ) {
            $check_results[$check] = $this->{$check}();
        }
        if ( $check_results['check_if_admin_call'] || !$check_results['check_if_protected'] || $check_results['check_if_admin_user_have_access'] ) {
            $show_paywall = false;
        }
        
        if ( isset( $_GET['wc_ppp_debug'] ) && $_GET['wc_ppp_debug'] === "true" ) {
            echo  '<pre>' ;
            var_dump( $check_results );
            echo  '</pre>' ;
        }
        
        if ( $check_results['check_if_has_access'] ) {
            $show_paywall = false;
        }
        if ( !$check_results['check_if_should_show_paywall'] ) {
            //This is to check for the delay restriction.
            $show_paywall = false;
        }
        return $show_paywall;
    }
    
    /***
     * Paywall Functions
     */
    /**
     * @return string
     */
    public function show_paywall( $unfiltered_content )
    {
        return $this->get_paywall_content( $unfiltered_content );
    }
    
    /**
     * @param $unfiltered_content
     *
     * @return string
     */
    public function show_content( $unfiltered_content )
    {
        $show_warnings = get_post_meta( $this->post_id, WC_PPP_SLUG . '_show_warnings', true );
        if ( 'expire' === $this->protection_type && !is_admin() && !$this->check_if_admin_user_have_access() && apply_filters( 'wc_pay_per_post_enable_javascript_expiration_refresh', true ) ) {
            $this->countdown_refresh();
        }
        
        if ( $show_warnings && !is_admin() ) {
            Woocommerce_Pay_Per_Post_Helper::logger( 'Page/Post has show_warnings enabled' );
            switch ( $this->protection_type ) {
                case 'page-view':
                    ob_start();
                    $template = 'pageview-status';
                    $user_info = $this->user_post_info;
                    Woocommerce_Pay_Per_Post_Helper::logger( 'Showing Pageview Status Warnings' );
                    $number_of_allowed_pageviews = get_post_meta( $this->post_id, WC_PPP_SLUG . '_page_view_restriction', true );
                    require ( locate_template( WC_PPP_TEMPLATE_PATH . $this->available_templates[$template] ) ?: plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/' . $this->available_templates[$template] );
                    $template_file = ob_get_clean();
                    return $template_file . $unfiltered_content;
                    break;
                case 'expire':
                    ob_start();
                    $template = 'expiration-status';
                    $user_info = $this->user_post_info;
                    Woocommerce_Pay_Per_Post_Helper::logger( 'Showing Expiration Status Warnings' );
                    require ( locate_template( WC_PPP_TEMPLATE_PATH . $this->available_templates[$template] ) ?: plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/' . $this->available_templates[$template] );
                    $template_file = ob_get_clean();
                    return $template_file . $unfiltered_content;
                    break;
                default:
                    return $unfiltered_content;
            }
        }
        
        return $unfiltered_content;
    }
    
    public function is_expired( $post_id )
    {
        return !$this->has_access_expiry_protection__premium_only( $post_id );
    }
    
    /***
     * PageView Protection Functions
     */
    /**
     * @param $post_id
     *
     * @return string
     */
    protected function get_paywall_content( $unfiltered_content )
    {
        $default_paywall_content = get_option( WC_PPP_SLUG . '_restricted_content_default', _x( "<h1>Oops, Restricted Content</h1><p>We are sorry but this post is restricted to folks that have purchased this page.</p>[products ids='{{product_id}}']", 'wc_pay_per_post' ) );
        $override_paywall_content = get_post_meta( $this->post_id, WC_PPP_SLUG . '_restricted_content_override', true );
        $product_ids = get_post_meta( $this->post_id, WC_PPP_SLUG . '_product_ids', true );
        $parent_id = wp_get_post_parent_id( $product_ids[0] );
        $product_ids = str_replace( ' ', '', $product_ids );
        $product_ids = implode( ',', (array) $product_ids );
        $excerpt = wp_trim_words( $unfiltered_content );
        $paywall_content = ( empty($override_paywall_content) ? $default_paywall_content : $override_paywall_content );
        $return_content = str_replace( '{{product_id}}', $product_ids, $paywall_content );
        $return_content = str_replace( '{{parent_id}}', $parent_id, $return_content );
        return do_shortcode( $return_content );
    }
    
    /**
     * @param $frequency
     * @param $date
     *
     * @return int
     */
    protected function get_time_difference( $frequency, $date )
    {
        $current_time = Woocommerce_Pay_Per_Post_Helper::current_time();
        switch ( $frequency ) {
            case 'minute':
                $difference = $current_time->diffInMinutes( $date );
                break;
            case 'hour':
                $difference = $current_time->diffInHours( $date );
                break;
            case 'day':
                $difference = $current_time->diffInDays( $date );
                break;
            case 'week':
                $difference = $current_time->diffInWeeks( $date );
                break;
            case 'month':
                $difference = $current_time->diffInMonths( $date );
                break;
            case 'year':
                $difference = $current_time->diffInYears( $date );
                break;
        }
        return $difference;
    }
    
    protected function countdown_refresh()
    {
        ?>
        <script>
          var countDownDate = new Date('<?php 
        echo  $this->user_post_info['expiration_date']->toRfc850String() ;
        ?>').getTime();
          var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = countDownDate - now;

            if (distance < 0) {
              clearInterval(x);
              location.reload(true);
            }
          }, 1000);
        </script>
		<?php 
    }

}