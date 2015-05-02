$(document).ready(function () {
    Page.init()
    Page.adjust()
    PrivateChat.init()
})

var Page = new function () {
    this.adjust = function () {
        $('#page').css({
            'min-height': $(window).height() - 67 + 'px'
        })
    }
    this.init = function () {
        $(window).resize(function () {
            Page.adjust()
        })

        $('#bg').scroll(function () {
            var x = $(this).scrollTop();
            $(this).css('background-position', '0% ' + parseInt(-x / 10) + 'px');
        })

        $('#envelope').css({
            right: $('#logout').width() + 37 + 'px'
        }).click(function () {
            window.location = '/' + lang + '/messages'
        })

        $('body').css({overflow: 'hidden'})
    }
}

