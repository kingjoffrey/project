$(document).ready(function () {
    Page.init()
    Page.adjust()
    PrivateChat.init()
})

var Page = new function () {
    var close = 0,
        changeCloseArrowLR = function (move, el) {
            if (move > 0) {
                $(el).html('&#x25C0');
            } else {
                $(el).html('&#x25B6');
            }
        }

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

        $('#friendsBox .close').click(function () {
            var left = $(this).parent().position().left,
                move = $(this).parent().width()

            if (close) {
                close = 0
            } else {
                move = -move
                close = 1
            }
            changeCloseArrowLR(move, this)
            $(this).parent().animate({left: left + move + 'px'}, 200)
        }).css({
            left: $('#friendsBox').width() + 10 + 'px'
        })
    }
}

