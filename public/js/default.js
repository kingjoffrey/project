$(document).ready(function () {
    Page.init()
    Chat.init()
})

var Page = new function () {
    var index = '',
        touch = 0,
        shadows = 1

    this.getIndex = function () {
        return index
    }
    this.getShadows = function () {
        return shadows
    }
    this.hasTouch = function () {
        return touch
    }
    this.adjust = function () {
        var height = $(window).height() - $('#menuBox').height(),
            top = height / 2 - $('#page #content #title div').height() / 2

        $('#page').css('min-height', $(window).height() + 'px')

        if (top > 0) {
            $('#page #content #title').css('margin-top', top + 'px')
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

        $('#messages').click(function () {
            WebSocketSendMain.controller('messages', 'index')
        })

        if (!index) {
            index = $('#content').html()
        }

        if (isSet(window.orientation)) {
            touch = 'ontouchstart' in document.documentElement
            console.log(touch)

            $(document).on('touchmove', function (e) {
                e.preventDefault()
            })

            shadows = 0
        }
    }
}
