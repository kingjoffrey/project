var Message = new function () {
    var maxHeight

    this.tutorial = function (title, goal, description) {
        var html = $('<div>')
            .append($('<div>').html(goal).addClass('goal'))
            .append($('<div>').html(description.description.replace("\n", '<br><br>')).addClass('description'))

        var id = this.show(title, html, 1)
        this.close(id)
    }
    this.simple = function (title, message) {
        var id = this.show(title, $('<div>').html(message).addClass('simple'))
        this.close(id)
        return id
    }
    this.error = function (message) {
        Sound.play('error')
        this.simple(translations.error, $('<div>').html(message).addClass('error'));
    }
    this.show = function (title, txt, tutorial) {
        this.remove()
        var id = makeId(10),
            div = $('<div>')
                .attr('id', id)
                .html($('<div>').addClass('content')
                    .append($('<div>').append($('<h3>').html(title)).addClass('msgTitle'))
                    .append($(txt).addClass('overflow'))
                )
                .addClass('message')
        if (isSet(tutorial)) {
            div.addClass('tutorial')
            $('.message.tutorial').remove()
        }
        if (div.find('.error').length) {
            div.addClass('error')
        }

        $('#main').append(div)
        Message.adjust(id)

        return id
    }
    this.remove = function (id) {
        if (isSet(id)) {
            $('#' + id).remove()
        } else {
            if (!Turn.isMy() && $('.message .showCastle').length) {
                $('.message:not(:has(.showCastle))').remove()
            } else {
                $('.message:not(.tutorial)').remove()
            }
        }
    }
    this.adjust = function (id) {
        if (isSet(id)) {
            if ($('#' + id + ' .showCastle').length) {
                $('#' + id + ' .content').css({
                    'z-index': $('#' + id + ' .content').css('z-index') + 100
                })
            }
            var element = $('#' + id + ' .content')
        } else {
            var element = $('.message .content')
        }

        var messageContentLeft = $(window).innerWidth() / 2 - element.outerWidth() / 2,
            messageContentTop = $(window).innerHeight() / 2 - element.outerHeight() / 2


        if (messageContentLeft < 0) {
            messageContentLeft = 0
        }
        if (messageContentTop < 0) {
            messageContentTop = 0
            var messageContentHeight = '100vh'
        } else {
            var messageContentHeight = 'auto'
        }

        element.css({
            'left': messageContentLeft + 'px',
            'top': messageContentTop + 'px',
            'height': messageContentHeight
        })
    }
    this.ok = function (id, func) {
        if (!$('#' + id + ' #buttons').length) {
            $('#' + id + ' .content').append($('<div>').attr('id', 'buttons'))
        }
        $('#' + id + ' #buttons').append(
            $('<div>')
                .addClass('button buttonColors go')
                .html(translations.ok)
                .click(function () {
                    if (isSet(func)) {
                        func();
                    }
                    Message.remove(id)
                })
        )

        if (CommonMe.isSelected()) {
            CommonMe.setIsSelected(0)
        }

        this.adjust(id)
    }
    this.cancel = function (id, func) {
        if (!$('#' + id + ' #buttons').length) {
            $('#' + id + ' .content').append($('<div>').attr('id', 'buttons'))
        }
        $('#' + id + ' #buttons').append(
            $('<div>')
                .addClass('button buttonColors cancel')
                .html(translations.cancel)
                .click(function () {
                    if (isSet(func)) {
                        func()
                    }
                    Message.remove(id)
                })
        )
        this.adjust(id)
    }
    this.close = function (id, func) {
        if (!$('#' + id + ' #buttons').length) {
            $('#' + id + ' .content').append($('<div>').attr('id', 'buttons'))
        }
        $('#' + id + ' #buttons').append(
            $('<div>')
                .addClass('button buttonColors')
                .attr('id', 'close')
                .html(translations.close)
                .click(function () {
                    if (isSet(func)) {
                        func()
                    }
                    Message.remove(id)
                })
        )
        this.adjust(id)
    }
}
