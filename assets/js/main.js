jQuery(function($){
    
    //Mobile menu, hamburger button.
    $('body').on('click', '.hamburger', function(e){
        e.preventDefault();
        $('nav#links').toggleClass('open');
    });
    
    //Forces the reset to empty the search field, not reset it to the PHP given value.
    $('.search-wrapper').on('click', '.reset', function(e){
       e.preventDefault();
       $(e.target).closest('.search-wrapper').find('.search-field').val('');
    });
    
});
