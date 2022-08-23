define([
    'jquery'
], function ($) {
    $.widget('IdeaInYou.closeMinSearch', {


        _create: function () {
            this.removeResult();
        },
        removeResult: function () {
            let mainClass = $(this.element);
            $(document).on('click', function(e) {
                if($(e.target).closest(mainClass).length !== 0){
                    mainClass.addClass('-opened');
                } else{
                    mainClass.removeClass('-opened');
                }
            });
        }
    });

    return $.IdeaInYou.closeMinSearch;
});
