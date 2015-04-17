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
        diff = 149

    this.init = function () {
        chatLeft = $(window).width() - $('#chatBox').width() - 12
        chatTop = $(window).height() - $('#chatBox').height() - 20


        $('#chatBox').css({
            left: chatLeft + 'px',
            top: chatTop + 'px'
        })

        $('#chatBox input').prop('disabled', true)

        $('#send').click(function () {
            Websocket.chat()
        })

        $('#friendsBox #friends div').click(function () {
            chatTop -= diff
            $('#chatBox').css({top: chatTop + 'px'})
            $('#chatBox').removeClass('mini')
            $('#chatBox #chatTitle').html($(this).html())
            $('#chatBox #friendId').val($(this).attr('id'))
            $('#chatBox input').prop('disabled', true)
            Chat.addCloseClick()
            $('#chatBox input').prop('disabled', false)
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
