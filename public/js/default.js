$(document).ready(function () {
    Page.init()
    Page.adjust()
    PrivateChat.init()
})

var Page = new function () {
    this.adjust = function () {
        var height = $(window).height()

        $('#page').css({
            'min-height': height - 67 + 'px'
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
            left: $('#logout').position().left - 37 + 'px'
        }).click(function () {
            window.location = '/' + lang + '/messages'
        })

        $('body').css({overflow: 'hidden'})
    }
}

