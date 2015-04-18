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
    var chatLeft,
        chatTop,
        diff = 149,
        inputWidth

    this.init = function () {
        chatLeft = $(window).width() - $('#chatBox').width()
        chatTop = $(window).height() - $('#chatBox').height()
        inputWidth = $('#chatBox input').width()

        $('#chatBox').css({
            left: chatLeft + 'px',
            top: chatTop + 'px'
        })

        $('#chatBox input').prop('disabled', true)

        $('#send').click(function () {
            Websocket.chat()
        })

        $('#friendsBox #friends div').click(function () {
            if ($('#chatBox').hasClass('mini')) {
                chatTop -= diff
                $('#chatBox').css({top: chatTop + 'px'})
            }
            $('#chatBox').removeClass('mini')
            $('#chatBox #chatTitle').html($(this).html())
            $('#chatBox #friendId').val($(this).attr('id'))
            Chat.addCloseClick()
            var padding = $('#chatBox #chatTitle').width() + 10
            $('#chatBox input')
                .prop('disabled', false)
                .css({
                    'padding-left': padding,
                    width: inputWidth - padding
                })
        })

    }
    this.addCloseClick = function () {
        $('#chatBox .close').click(function () {
            chatTop += diff
            $('#chatBox').css({top: chatTop + 'px'})
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
