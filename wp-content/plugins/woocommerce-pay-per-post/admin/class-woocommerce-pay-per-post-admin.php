<?php

use  PRAMADILLO\INTEGRATIONS\PaidMembershipsPro ;
use  PRAMADILLO\INTEGRATIONS\WooCommerceMemberships ;
use  PRAMADILLO\INTEGRATIONS\WooCommerceSubscriptions ;
class Woocommerce_Pay_Per_Post_Admin
{
    public  $integrations = array() ;
    private  $allowed_restriction_frequency = array(
        'minute',
        'hour',
        'day',
        'week',
        'month',
        'year'
    ) ;
    public function __construct()
    {
    }
    
    public function add_plugin_options()
    {
        global  $wp_version ;
        add_menu_page(
            __( 'Pay For Post with WooCommerce', 'wc_pay_per_post' ),
            'Pay For Post',
            'manage_options',
            WC_PPP_SLUG,
            array( $this, 'create_options_page' ),
            'dashicons-cart',
            99
        );
        add_submenu_page(
            WC_PPP_SLUG,
            'Settings',
            'Settings',
            'manage_options',
            WC_PPP_SLUG . '-settings',
            array( $this, 'create_options_page' )
        );
        add_submenu_page(
            WC_PPP_SLUG,
            'What\'s New',
            'What\'s New',
            'manage_options',
            WC_PPP_SLUG . '-whats-new',
            array( $this, 'create_whatsnew_page' )
        );
        add_submenu_page(
            WC_PPP_SLUG,
            'Getting Started with Pay For Post with WooCommerce',
            'Getting Started',
            'manage_options',
            WC_PPP_SLUG . '-getting-started',
            array( $this, 'create_getting_started_page' )
        );
        add_submenu_page(
            WC_PPP_SLUG,
            'Pay For Post with WooCommerce Documentation',
            'Documentation',
            'manage_options',
            WC_PPP_SLUG . '-help',
            array( $this, 'create_help_page' )
        );
        if ( version_compare( $wp_version, '5.2.0', '>=' ) ) {
            add_submenu_page(
                WC_PPP_SLUG,
                'Debug',
                'Debug',
                'manage_options',
                WC_PPP_SLUG . '-debug',
                array( $this, 'create_debug_page' )
            );
        }
        add_submenu_page(
            WC_PPP_SLUG,
            '',
            '<span class="pramadillo-admin-separator-container"><span class="pramadillo-admin-separator-title">Account Info</span><span class="pramadillo-admin-separator"></span></span>',
            'manage_options',
            '#'
        );
        remove_submenu_page( WC_PPP_SLUG, WC_PPP_SLUG );
    }
    
    public function enqueue_scripts()
    {
        wp_register_script(
            WC_PPP_SLUG . '_admin',
            plugin_dir_url( __FILE__ ) . 'js/wc-ppp-admin.js',
            array( 'jquery' ),
            WC_PPP_VERSION,
            false
        );
        wp_register_script(
            WC_PPP_SLUG . '_select2',
            plugin_dir_url( __FILE__ ) . 'js/select2.min.js',
            array(),
            '4.0.6',
            false
        );
    }
    
    public function enqueue_styles()
    {
        wp_register_style(
            WC_PPP_SLUG . '_admin',
            plugin_dir_url( __FILE__ ) . 'css/wc-ppp-admin.css',
            array(),
            WC_PPP_VERSION,
            'all'
        );
        wp_register_style(
            WC_PPP_SLUG . '_select2',
            plugin_dir_url( __FILE__ ) . 'css/select2.min.css"',
            array(),
            WC_PPP_VERSION,
            'all'
        );
    }
    
    public function admin_menu_separator_styles()
    {
        ?>
        <style>
            .pramadillo-admin-separator-container {
                display: flex;
                height: 12px;
                align-items: center;
                margin: 0 -10px 0 0;
            }

            .pramadillo-admin-separator-container .pramadillo-admin-separator-title {
                font-size: .68em;
                text-transform: uppercase;
                font-weight: 700;
                margin-right: 10px;
                color: hsla(0, 0%, 100%, .25);
            }

            .pramadillo-admin-separator-container .pramadillo-admin-separator {
                display: block;
                flex: 1;
                padding: 0;
                height: 1px;
                line-height: 1px;
                background: hsla(0, 0%, 100%, .125);
            }
        </style>
		<?php 
    }
    
    public function ajax_post_types()
    {
        $post_types = array();
        foreach ( get_post_types( array(
            'public' => true,
        ), 'names' ) as $post_type ) {
            $post_types[] = $post_type;
        }
        return $post_types;
    }
    
    public function options_init()
    {
        register_setting( WC_PPP_SLUG . '_settings', WC_PPP_SLUG . '_restricted_content_default' );
        register_setting( WC_PPP_SLUG . '_settings', WC_PPP_SLUG . '_custom_post_types' );
        register_setting( WC_PPP_SLUG . '_settings', WC_PPP_SLUG . '_only_show_virtual_products' );
        register_setting( WC_PPP_SLUG . '_settings', WC_PPP_SLUG . '_turn_off_comments_when_protected' );
        register_setting( WC_PPP_SLUG . '_settings', WC_PPP_SLUG . '_allow_admins_access_to_protected_posts' );
        register_setting( WC_PPP_SLUG . '_settings', WC_PPP_SLUG . '_enable_debugging' );
        register_setting( WC_PPP_SLUG . '_settings', WC_PPP_SLUG . '_delete_settings' );
    }
    
    public function create_options_page()
    {
        global  $wcppp_freemius ;
        $restricted_content_default = get_option( WC_PPP_SLUG . '_restricted_content_default', _x( "<h1>Oops, Restricted Content</h1>\n<p>We are sorry but this post is restricted to folks that have purchased this page.</p>\n\n[products ids='{{product_id}}']", 'Default restricted content', 'wc_pay_per_post' ) );
        $custom_post_types = get_option( WC_PPP_SLUG . '_custom_post_types', array() );
        $custom_post_types = ( empty($custom_post_types) ? array() : $custom_post_types );
        if ( !is_array( $custom_post_types ) ) {
            $custom_post_types = explode( ',', $custom_post_types );
        }
        $turn_off_comments_when_protected = get_option( WC_PPP_SLUG . '_turn_off_comments_when_protected', true );
        $allow_admins_access_to_protected_posts = get_option( WC_PPP_SLUG . '_allow_admins_access_to_protected_posts', false );
        $enable_debugging = get_option( WC_PPP_SLUG . '_enable_debugging', false );
        $delete_settings = get_option( WC_PPP_SLUG . '_delete_settings', false );
        $available_post_types = $this->ajax_post_types();
        $only_show_virtual_products = get_option( WC_PPP_SLUG . '_only_show_virtual_products', false );
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( esc_html( 'You do not have sufficient permissions to access this page.' ) );
        }
        wp_enqueue_style( WC_PPP_SLUG . '_admin' );
        wp_enqueue_style( WC_PPP_SLUG . '_select2' );
        wp_enqueue_script( WC_PPP_SLUG . '_select2' );
        wp_enqueue_script( WC_PPP_SLUG . '_admin' );
        //Delete Log Functionality
        
        if ( isset( $_GET['delete_log_nonce'] ) && wp_verify_nonce( $_GET['delete_log_nonce'], 'delete_log' ) ) {
            $log = new Woocommerce_Pay_Per_Post_Logger();
            $log->delete_log_file();
            echo  '<script>alert("Log File Deleted");</script>' ;
        }
        
        require_once plugin_dir_path( __FILE__ ) . 'partials/settings.php';
    }
    
    public function create_help_page()
    {
        wp_enqueue_style( WC_PPP_SLUG . '_admin' );
        wp_enqueue_style( WC_PPP_SLUG . '_select2' );
        wp_enqueue_script( WC_PPP_SLUG . '_select2' );
        wp_enqueue_script( WC_PPP_SLUG . '_admin' );
        require_once plugin_dir_path( __FILE__ ) . 'partials/help.php';
    }
    
    public function create_debug_page()
    {
        wp_enqueue_style( WC_PPP_SLUG . '_admin' );
        
        if ( isset( $_POST['wc_ppp_create_table_nonce'] ) && wp_verify_nonce( $_POST['wc_ppp_create_table_nonce'], 'wc_ppp_create_table' ) ) {
            require_once WC_PPP_PATH . '/includes/class-woocommerce-pay-per-post-activator.php';
            Woocommerce_Pay_Per_Post_Activator::create_table__premium_only();
        }
        
        require_once plugin_dir_path( __FILE__ ) . 'partials/debug.php';
    }
    
    public function create_getting_started_page()
    {
        wp_enqueue_style( WC_PPP_SLUG . '_admin' );
        require_once plugin_dir_path( __FILE__ ) . 'partials/getting-started.php';
    }
    
    public function create_whatsnew_page()
    {
        wp_enqueue_style( WC_PPP_SLUG . '_admin' );
        $needs_upgrade = get_option( WC_PPP_SLUG . '_needs_upgrade', 'true' );
        $custom_post_types = get_option( WC_PPP_SLUG . '_custom_post_types', array() );
        $custom_post_types = ( empty($custom_post_types) ? array() : $custom_post_types );
        if ( !is_array( $custom_post_types ) ) {
            $custom_post_types = explode( ',', $custom_post_types );
        }
        $old_products = new WP_Query( array(
            'post_type' => $custom_post_types,
            'meta_key'  => 'woocommerce_ppp_product_id',
            'nopaging'  => true,
        ) );
        if ( isset( $_POST['wc_ppp_upgrade_nonce'] ) && wp_verify_nonce( $_POST['wc_ppp_upgrade_nonce'], 'wc_ppp_upgrade' ) ) {
            $this->upgrade_database( $old_products );
        }
        $readme = file_get_contents( WC_PPP_PATH . 'README.txt' );
        $changelog = explode( '== Changelog ==', $readme );
        $changelog = explode( '== ', $changelog[1] );
        $full_change_log = $changelog[0];
        $last_change = explode( '=', $full_change_log );
        $last_change = $last_change[2];
        require_once plugin_dir_path( __FILE__ ) . 'partials/whats-new.php';
    }
    
    public function meta_box()
    {
        $post_types = $this->get_post_types();
        foreach ( $post_types as $post_type ) {
            add_meta_box(
                WC_PPP_SLUG . '_meta_box',
                __( 'Pay For Post with WooCommerce', 'wc_pay_per_post' ),
                array( $this, 'output_meta_box' ),
                $post_type,
                'normal',
                'high',
                array(
                '__block_editor_compatible_meta_box' => true,
            )
            );
        }
    }
    
    public function get_post_types()
    {
        $user_included_post_types = get_option( WC_PPP_SLUG . '_custom_post_types', array() );
        if ( '' === $user_included_post_types || empty($user_included_post_types) ) {
            $user_included_post_types = array();
        }
        return (array) $user_included_post_types;
    }
    
    public function output_meta_box()
    {
        ob_start();
        global  $post ;
        $id = $post->ID;
        $selected = get_post_meta( $id, WC_PPP_SLUG . '_product_ids', true );
        $restricted_content_override = get_post_meta( $id, WC_PPP_SLUG . '_restricted_content_override', true );
        $delay_restriction_enable = get_post_meta( $id, WC_PPP_SLUG . '_delay_restriction_enable', true );
        $delay_restriction = get_post_meta( $id, WC_PPP_SLUG . '_delay_restriction', true );
        $delay_restriction_frequency = get_post_meta( $id, WC_PPP_SLUG . '_delay_restriction_frequency', true );
        $page_view_restriction_enable = get_post_meta( $id, WC_PPP_SLUG . '_page_view_restriction_enable', true );
        $page_view_restriction = get_post_meta( $id, WC_PPP_SLUG . '_page_view_restriction', true );
        $page_view_restriction_frequency = get_post_meta( $id, WC_PPP_SLUG . '_page_view_restriction_frequency', true );
        $page_view_restriction_enable_time_frame = get_post_meta( $id, WC_PPP_SLUG . '_page_view_restriction_enable_time_frame', true );
        $page_view_restriction_time_frame = get_post_meta( $id, WC_PPP_SLUG . '_page_view_restriction_time_frame', true );
        $expire_restriction_enable = get_post_meta( $id, WC_PPP_SLUG . '_expire_restriction_enable', true );
        $expire_restriction = get_post_meta( $id, WC_PPP_SLUG . '_expire_restriction', true );
        $expire_restriction_frequency = get_post_meta( $id, WC_PPP_SLUG . '_expire_restriction_frequency', true );
        $show_warnings = get_post_meta( $id, WC_PPP_SLUG . '_show_warnings', true );
        $drop_down = $this->generate_products_dropdown( $selected );
        wp_enqueue_style( WC_PPP_SLUG . '_admin' );
        wp_enqueue_style( WC_PPP_SLUG . '_select2' );
        wp_enqueue_script( WC_PPP_SLUG . '_select2' );
        wp_enqueue_script( WC_PPP_SLUG . '_admin' );
        require plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/meta-box-base.php';
        echo  ob_get_clean() ;
    }
    
    public function get_all_product_args( $args )
    {
        return $args;
    }
    
    public function get_virtual_product_args( $args )
    {
        return $args;
    }
    
    public function save_meta_box( $post_id )
    {
        // Stop the script when doing autosave.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        // Verify the nonce. If insn't there, stop the script.
        if ( !isset( $_POST[WC_PPP_SLUG . '_nonce'] ) || !wp_verify_nonce( $_POST[WC_PPP_SLUG . '_nonce'], WC_PPP_SLUG . '_nonce' ) ) {
            return;
        }
        // Stop the script if the user does not have edit permissions.
        if ( !current_user_can( 'edit_posts' ) ) {
            return;
        }
        // Boolean Values.
        $_delay_restriction_enable = 0;
        $_page_view_restriction_enable = 0;
        $_page_view_restriction_enable_time_frame = 0;
        $_expire_restriction_enable = 0;
        $_show_warnings = 0;
        // Save the product_id's associated with page/post.
        $product_ids = ( isset( $_POST[WC_PPP_SLUG . '_product_ids'] ) ? $_POST[WC_PPP_SLUG . '_product_ids'] : '' );
        $product_ids = $this->sanitize_product_ids( $product_ids );
        update_post_meta( $post_id, WC_PPP_SLUG . '_product_ids', $product_ids );
    }
    
    public function manage_custom_column( $column, $post_id )
    {
        
        if ( $column === WC_PPP_SLUG . '_protected' ) {
            $protected = Woocommerce_Pay_Per_Post_Helper::is_protected( $post_id );
            if ( $protected ) {
                echo  Woocommerce_Pay_Per_Post_Helper::protection_display_icon( $protected ) ;
            }
        }
    
    }
    
    public function manage_columns( $columns )
    {
        $columns[WC_PPP_SLUG . '_protected'] = 'Pay For Post';
        return $columns;
    }
    
    public function sortable_columns( $columns )
    {
        $columns[WC_PPP_SLUG . '_protected'] = WC_PPP_SLUG . '_protected';
        return $columns;
    }
    
    public function plugin_settings_link( $links )
    {
        $url = admin_url( 'options-general.php?page=' . WC_PPP_SLUG );
        $_link = '<a href="' . $url . '">' . __( 'Settings', 'wc_pay_per_post' ) . '</a>';
        $links[] = $_link;
        return $links;
    }
    
    public function prefix_plugin_update_message( $data, $response )
    {
        if ( isset( $data['upgrade_notice'] ) ) {
            printf( '<div class="update-message">%s</div>', esc_html( wpautop( $data['upgrade_notice'] ) ) );
        }
    }
    
    protected function upgrade_database( $products )
    {
        foreach ( $products->posts as $post ) {
            // Get old meta key for product id's associated with posts.
            $post_meta = get_post_meta( $post->ID, 'woocommerce_ppp_product_id', true );
            
            if ( '' !== $post_meta ) {
                // Added in to account for fields that were there but with no products associated with them.
                $old_ppp_ids = explode( ',', $post_meta );
                update_post_meta( $post->ID, WC_PPP_SLUG . '_product_ids', $old_ppp_ids );
            }
        
        }
        update_option( 'wc_pay_per_post_needs_upgrade', 'false', false );
        update_option( 'wc_pay_per_post_db_version', WC_PPP_PLUGIN_VERSION, false );
        $url = admin_url( 'admin.php?page=' . WC_PPP_SLUG . '-whats-new&upgrade_complete=true' );
        wp_safe_redirect( $url );
    }
    
    protected function get_post_products_custom_field( $value, $id = null )
    {
        $custom_field = get_post_meta( $id, $value, true );
        
        if ( !empty($custom_field) ) {
            return ( is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) ) );
        } else {
            return false;
        }
    
    }
    
    protected function generate_products_dropdown( $selected = array() )
    {
        $only_show_virtual_products = (bool) get_option( WC_PPP_SLUG . '_only_show_virtual_products', false );
        
        if ( $only_show_virtual_products ) {
            $products = apply_filters( 'wc_pay_per_post_get_virtual_products', $this->get_virtual_products() );
        } else {
            $products = apply_filters( 'wc_pay_per_post_get_all_products', $this->get_all_products() );
        }
        
        $drop_down = '<select id="' . WC_PPP_SLUG . '_product_ids" name="' . WC_PPP_SLUG . '_product_ids[]" style="width: 100%" multiple="multiple">';
        $drop_down .= '<optgroup label="Products">';
        foreach ( $products as $product ) {
            $drop_down .= '<option value="' . $product['ID'] . '"';
            if ( in_array( (string) $product['ID'], (array) $selected, true ) ) {
                $drop_down .= ' selected="selected"';
            }
            $drop_down .= '>' . $product['post_title'] . ' - [#' . $product['ID'] . ']</option>';
        }
        $drop_down .= '</optgroup>';
        $drop_down .= '</select>';
        return $drop_down;
    }
    
    protected function get_all_products()
    {
        $args = array(
            'post_type'   => [ 'product' ],
            'orderby'     => 'title',
            'post_status' => 'publish',
            'order'       => 'ASC',
            'nopaging'    => true,
        );
        $products = get_posts( apply_filters( 'wc_pay_per_post_all_product_args', $args ) );
        $return = array();
        foreach ( $products as $product ) {
            $return[] = [
                'ID'         => $product->ID,
                'post_title' => $product->post_title,
            ];
        }
        return $return;
    }
    
    protected function get_virtual_products()
    {
        $args = array(
            'post_type'   => [ 'product' ],
            'post_status' => 'publish',
            'orderby'     => 'title',
            'order'       => 'ASC',
            'nopaging'    => true,
        );
        $products = get_posts( apply_filters( 'wc_pay_per_post_virtual_product_args', $args ) );
        $return = array();
        foreach ( $products as $product ) {
            $is_virtual = get_post_meta( $product->ID, '_virtual', true );
            $is_downloadable = get_post_meta( $product->ID, '_downloadable', true );
            if ( 'yes' === $is_virtual || '1' == $is_virtual || ('yes' === $is_downloadable || '1' == $is_downloadable) ) {
                $return[] = [
                    'ID'         => $product->ID,
                    'post_title' => $product->post_title,
                ];
            }
        }
        return $return;
    }
    
    private function sanitize_product_ids( $product_ids )
    {
        
        if ( is_array( $product_ids ) ) {
            $return = array();
        } else {
            return '';
        }
        
        foreach ( $product_ids as $key => $product_id ) {
            if ( is_numeric( $product_id ) ) {
                $return[] = $product_id;
            }
        }
        return $return;
    }

}