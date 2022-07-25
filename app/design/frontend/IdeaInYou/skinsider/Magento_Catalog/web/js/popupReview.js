define([
    'jquery'
], function ($) {
    $.widget('IdeaInYou.popupReview', {


        _create: function () {
            this.clickText();
            this.clickButton();
            this.clickClose();
            this.clickBackClose();
        },
        clickText: function () {
            let self = $(this.element),
                clickText = $(this.element).find(".rating-write .add");
            clickText.on('click', function (){
                self.parent().find("#tab-label-reviews").click();
                $('.page-main').toggleClass('popup');
                self.parent().find(".block.review-add").toggleClass('show');
                $('body').css("overflow-y", "hidden");
            });
        },
        clickButton: function () {
            let self = $(this.element),
                classPopup = (".block.review-add > .button-review-popup"),
                clickButton = $(this.element).parent().find(classPopup);
            clickButton.on('click', function (){
                self.parent().find(".block.review-add").toggleClass('show');
                $('.page-main').toggleClass('popup');
                $('body').css("overflow-y", "hidden");
            });
        },
        clickClose: function () {
            let self = $(this.element),
                classClose = (".title-review > .close-button-review"),
                clickClose = $(this.element).parent().find(".block.review-add > .block-content").find(classClose);
            clickClose.on('click', function (){
                self.parent().find(".block.review-add").removeClass('show');
                $('.page-main').removeClass('popup');
                $('body').css("overflow-y", "unset");
            });
        },
        clickBackClose: function () {
             let clickBackClose = $(this.element).parent().find(".block.review-add > .background-review");
            clickBackClose.on('click', function (){
                $(this).parent().removeClass('show');
                $('.page-main').removeClass('popup');
                $('body').css("overflow-y", "unset");
            });
        }
    });
    return $.IdeaInYou.popupReview;
});
