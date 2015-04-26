$(document).ready(function () {
    Page.init()
    Page.adjust()
    Chat.init()
})

var Page = new function () {
    this.adjust = function () {
        var height = $(window).height()

        $('#page').css({
            'min-height': height + 'px'
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
            left: $('#menuTop #title').offset().left + $('#menuTop #title').width() + 'px'
        }).click(function () {
            window.location = '/' + lang + '/messages'
        })

        $('body').css({overflow: 'hidden'})
    }
}

