jQuery(document).ready(function(){

    // jQuery(document).on('click','.ant',function(){
    //     plusSlides(-1);
    // });
    // jQuery(document).on('click','.sig',function(){
    //     plusSlides(1);
       
    // });
    var slideIndex = 1;
    showSlides(slideIndex);

    function plusSlides(n) {
        showSlides(slideIndex += n);
    }
    function showSlides(n) {
        var i;
        var slides =jQuery(".slider-a");
        if (n > slides.length) {slideIndex = 1;}    
        if (n < 1) {slideIndex = slides.length}
        jQuery(".slider-a").each(function(){
            jQuery(this).css('display','none');

        });
        //var s=jQuery(".slider-a").get(slideIndex);
        var d=jQuery(".slider-a").get(slideIndex-1);
        jQuery(d).css('display','block');            
    }
    // function slider(){
    //     setTimeout(() => {
    //         plusSlides(1);
    //         document.functions.slider();
    //     }, 1000);
    // }
    function slider() {
        plusSlides(1);
        setTimeout(slider, 3000);
    }
    
    setTimeout(slider, 3000);
    
});