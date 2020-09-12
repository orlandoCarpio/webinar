<div class="services-grid-v">
    
    <?php 
        if ( has_post_thumbnail() && $args['thumbnail'] == true): ?>
        <div class="image-v">
        <?php echo get_the_post_thumbnail(get_the_id(),array($args['tamano'],$args['tamano']),array('title'=>get_the_title(),'alt'=>get_the_title(),'class'=>'imageborder'));?>
            <div class="hoverimage"></div>
        </div>
    <?php endif; ?>
  
    <div class="posts_content-v">
        <a href="<?php echo get_permalink(); ?>" title="<?php sprintf( "Enlace permanente a %s", get_the_title() );?>">
            <h3><?php echo wp_html_excerpt (get_the_title(), $args['longitud_titulo'] );?></h3>
        </a>
        <?php $excerpt=get_the_excerpt();?>
        <?php $salida = ($excerpt)?'<p>'.wp_html_excerpt($excerpt,$args['longitud_desc']).'...</p>':'';
            echo $salida;
        ?>
        <a href="<?php echo get_permalink(); ?>"><button type="button" class="btn btn-primary btn-sm">Leer más...</button></a>
    </div>
</div>