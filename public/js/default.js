$(document).ready(function () {
    Page.init()
    Page.adjust()
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
        var padding = 360,
            height = $(window).height() - padding,
            top = height / 2 - 192 / 2

        $('#page').css('min-height', height + 'px')
        if (top > 0) {
            $('#bg #page #content #title').css('margin-top', top + 'px')
        }
    }
    this.init = function () {
        $(window).resize(function () {
            Page.adjust()
        })

        $('#bg').scroll(function () {
            var x = $(this).scrollTop();
            $(this).css('background-position', '0% ' + parseInt(-x / 10) + 'px');
        })

        $('#envelope').click(function () {
            WebSocketSendMain.controller('messages', 'index')
        })

        $('#findFriends').click(function () {
            WebSocketSendMain.controller('players', 'index')
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
