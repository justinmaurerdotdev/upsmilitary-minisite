$('document').ready(function(){

    $(document).on('click','.veteranGateway .expand-button', function(){
        console.log('now');
        $(this).parent('.expander__wrapper').toggleClass('open-expander');
        $(this).siblings('.hidden-part').slideToggle(function(){
            $(this).parent('.expander__wrapper').trigger('classChange');
        });
        $(this).children('i.fa').toggleClass('fa-plus').toggleClass('fa-minus');
    });
    $('#modal').on('click','.expand-button',function(){
        $(this).parent('.expander__wrapper').toggleClass('open-expander').trigger('classChange');
        $(this).siblings('.hidden-part').slideToggle(function(){
            $(this).parent('.expander__wrapper').trigger('classChange');
        });
        $(this).children('i.fa').toggleClass('fa-plus').toggleClass('fa-minus');
    });
});
