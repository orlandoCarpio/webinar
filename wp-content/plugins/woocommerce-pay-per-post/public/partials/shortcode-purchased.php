<div class="wc-ppp-purchased-container">
	<?php 

if ( count( $purchased ) > 0 ) {
    ?>
        <ul id="what-ever-you-want">
			<?php 
    foreach ( $purchased as $post ) {
        ?>
                <li>
                    <a href="<?php 
        echo  esc_url( get_permalink( $post->ID ) ) ;
        ?>"><?php 
        echo  esc_html( $post->post_title ) ;
        ?></a>
                </li>
			<?php 
    }
    ?>
        </ul>
	<?php 
} else {
    ?>
        <p><?php 
    esc_attr_e( 'You have not purchased any protected posts.', 'wc_pay_per_post' );
    ?></p>
	<?php 
}

?>
</div>