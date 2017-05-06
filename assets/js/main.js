jQuery(function($){

    //Mobile menu, hamburger button.
    $('body').on('click', '.hamburger', function(e){
        e.preventDefault();
        $('nav#links').toggleClass('open');
    });

    // Show/hide the reset link if the search field is populated/empty
    function updateResetButton() {
        if ($('.search-field').val()) {
            $('.reset').show();
        } else {
            $('.reset').hide();
        }
    }
    $('body').on('keyup', '.search-field', updateResetButton)

    updateResetButton();

    // Makes the entire profile card clickable...
    $('body').on('click', '.profile', function(event) {
        window.open(event.currentTarget.dataset.href, '_blank');
    });

    // ...while keeping inner a tags clickable
    $('body').on('click', '.profile a', function(event) {
        event.stopPropagation();
    })
});
