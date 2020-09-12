<div class="contenedor-blog">
    <div class="left">
        <div class="post-blog">
            
            <?php echo do_shortcode('[recientes-blog  limite="3" longitud_titulo="50" longitud_desc="100" thumbnail="1" tamano="50" categoria="4"]');?>
            
        </div>
    </div>
    <div class="right">
        <?php dynamic_sidebar( 'buscar-post' ); ?>
        <?php echo do_shortcode('[categoria-blog]');?>
    </div>
</div>