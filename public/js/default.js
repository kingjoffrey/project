$(document).ready(function () {

    Page.adjust()

    $(window).resize(function () {
        Page.adjust()
    })

    $('#bg').scroll(function () {
        var x = $(this).scrollTop();
        $(this).css('background-position', '0% ' + parseInt(-x / 10) + 'px');
    })

    $('body').css({overflow: 'hidden'})

    Websocket.init('chat')
    Chat.init()
})

var Chat = new function () {
    var inputWidth

    this.init = function () {
        inputWidth = $('#chatBox input').width()

        $('#chatBox input').prop('disabled', true)

        $('#send').click(function () {
            Websocket.chat()
        })

        $('#friendsBox #friends div').click(function () {
            Chat.prepare($(this).html(), $(this).attr('id'))
        })

    }
    this.message = function (to, chatTitle, msg) {
        if (to) {
            var prepend = translations.to
        } else {
            var prepend = translations.from
        }
        $('#chatContent').append(prepend + ' ' + chatTitle + ': ' + msg + '<br/>')
    }
    this.prepare = function (name, friendId) {
        $('#chatBox').removeClass('mini')
        if (isSet(name)) {
            $('#chatBox #chatTitle').html(name)
            if (isSet(friendId)) {
                $('#chatBox #friendId').val(friendId)
            }
            var padding = $('#chatBox #chatTitle').width() + 10
            $('#chatBox input')
                .prop('disabled', false)
                .css({
                    'padding-left': padding,
                    width: inputWidth - padding
                })
        }
        Chat.addCloseClick()
    }
    this.addCloseClick = function () {
        $('#chatBox .close').click(function () {
            $('#chatBox').addClass('mini')
            Chat.removeCloseClick()
        })
    }
    this.removeCloseClick = function () {
        $('#chatBox input').prop('disabled', true)
        $('#chatBox .close').unbind()
    }
}

var Page = {
    adjust: function () {
        var height = $(window).height()

        $('#page').css({
            'min-height': height + 'px'
        })
    }
}
