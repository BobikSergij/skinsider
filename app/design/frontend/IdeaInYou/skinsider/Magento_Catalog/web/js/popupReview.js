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
            const self = $(this.element),
                  parent = self.parent(),
                  clickText = parent.find(".rating-write .add");
            clickText.on('click', function (){
                parent.parent().find("#tab-label-reviews").click();
                $('.page-main').toggleClass('popup');
                parent.parent().find(".block.review-add").toggleClass('show');
                $('body').css("overflow-y", "hidden");
            });
        },
        clickButton: function () {
            let self = $(this.element),
                parent = self.parent(),
                classPopup = (".block.review-add > .button-review-popup"),
                clickButton = parent.parent().find(classPopup);
            clickButton.on('click', function (){
                parent.parent().find(".block.review-add").toggleClass('show');
                $('.page-main').toggleClass('popup');
                $('body').css("overflow-y", "hidden");
            });
        },
        clickClose: function () {
            let self = $(this.element),
                parent = self.parent(),
                classClose = (".title-review > .close-button-review"),
                clickClose = parent.parent().find(".block.review-add > .block-content").find(classClose);
            clickClose.on('click', function (){
                parent.parent().find(".block.review-add").removeClass('show');
                $('.page-main').removeClass('popup');
                $('body').css("overflow-y", "unset");
            });
        },
        clickBackClose: function () {
            const self = $(this.element),
                  parent = self.parent(),
                  clickBackClose = parent.parent().find(".block.review-add > .background-review");
            clickBackClose.on('click', function (){
                $(this).parent().removeClass('show');
                $('.page-main').removeClass('popup');
                $('body').css("overflow-y", "unset");
            });
        }
    });
    return $.IdeaInYou.popupReview;
});
