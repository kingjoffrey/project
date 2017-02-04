var Message = new function () {
    var maxHeight

    this.tutorial = function (title, message) {
        var id = this.show(title, $('<div>').html(message), 1)
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
                .fadeIn(200)
                .addClass('message')
        if (isSet(tutorial)) {
            div.addClass('tutorial')
            $('.message.tutorial').remove()
        }
        if (div.find('.error').length) {
            div.addClass('error')
        }
        $('#main').append(div)
        this.adjust(id)
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

            var messageContentLeft = $(window).innerWidth() / 2 - $('#' + id + ' .content').outerWidth() / 2,
                messageContentTop = $(window).innerHeight() / 2 - $('#' + id + ' .content').outerHeight() / 2

            $('#' + id + ' .content').css({
                'left': messageContentLeft + 'px',
                'top': messageContentTop + 'px'
            })
        } else {
            var messageContentLeft = $(window).innerWidth() / 2 - $('.message .content').outerWidth() / 2,
                messageContentTop = $(window).innerHeight() / 2 - $('.message .content').outerHeight() / 2

            $('.message .content').css({
                'left': messageContentLeft + 'px',
                'top': messageContentTop + 'px'
            })
        }
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
