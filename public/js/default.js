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

    $('#chatInput').css({
        width: $('#chatBox input').width() + $('#chatBox #send').width() + 60 + 'px'
    })

    var chatLeft = $(window).width() - $('#chatBox').width() - 12,
        chatTop = $(window).height() - $('#chatBox').height() - 20

    $('#chatBox').css({
        left: chatLeft + 'px',
        top: chatTop + 'px'
    })


    Websocket.init('chat')

    $('#send').click(function () {
        Websocket.chat()
    })

    $('#friendsBox #friends div').click(function () {
        $('#chatBox').css({
            display: 'block'
        })
        $('#chatBox #chatTitle').css({
            display: 'block'
        }).html($(this).html())
        $('#chatBox #friendId').val($(this).attr('id'))
    })
})

var Page = {
    adjust: function () {
        var height = $(window).height()

        $('#page').css({
            'min-height': height + 'px'
        })
    }
}
