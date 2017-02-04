var EditorMessage = new function () {
    var max = false,
        maxHeight,
        maxWidth,
        element = '#mainMenu'

    this.remove = function (id) {
        if (isSet(id)) {
            $('#' + id).fadeOut(200, function () {
                $('#' + id).remove()
            })
        } else {
            $('.message').remove()
        }
    }
    this.show = function (title, txt) {
        this.remove()
        var id = makeId(10)
        $(element).after(
            $('<div>')
                .addClass('message')
                .attr('id', id)
                .append($('<div>').attr('id', 'content')
                    .append($('<div>').append($('<h3>').html(title)).addClass('msgTitle'))
                    .append($(txt).addClass('overflow'))
                )
                .fadeIn(200)
        )
        this.adjust(id)
        return id
    }
    this.adjust = function (id) {
        maxHeight = $(window).innerHeight() - 140
        maxWidth = $(window).innerWidth() - 40

        if (maxHeight < parseInt($('#' + id).css('min-height'))) {
            maxHeight = parseInt($('#' + id).css('min-height'))

        }
        if (maxWidth < parseInt($('#' + id).css('min-width'))) {
            maxWidth = parseInt($('#' + id).css('min-width'))
        }

        if ($('#' + id + ' .showCastle').length) {
            $('#' + id).css({
                'z-index': $('#' + id).css('z-index') + 1,
                left: $(window).innerWidth() / 2 - $('#' + id).outerWidth() / 2 + 'px',
                'max-width': maxWidth + 'px',
                'max-height': maxHeight + 'px'
            })
            $('#' + id + ' .msgTitle').css({width: $('#' + id).width() - 50})
        } else {
            $('.message').css({
                left: $(window).innerWidth() / 2 - $('.message').outerWidth() / 2 + 'px',
                'max-width': maxWidth + 'px',
                'max-height': maxHeight + 'px'
            })
            $('.message .msgTitle').css({width: $('.message').width() - 50})
        }
    }
    this.ok = function (id, func) {
        if (!$('#' + id + ' #buttons').length) {
            $('#' + id).append($('<div>').attr('id', 'buttons'))
        }
        $('#' + id + ' #buttons').append(
            $('<div>')
                .addClass('button buttonColors go')
                .html(translations.ok)
                .click(function () {
                    if (isSet(func)) {
                        func();
                    }
                    EditorMessage.remove(id);
                })
        )
    }
    this.cancel = function (id, func) {
        if (!$('#' + id + ' #buttons').length) {
            $('#' + id).append($('<div>').attr('id', 'buttons'))
        }
        $('#' + id + ' #buttons').append(
            $('<div>')
                .addClass('button buttonColors cancel')
                .html(translations.cancel)
                .click(function () {
                    if (isSet(func)) {
                        func()
                    }
                    EditorMessage.remove(id)
                })
        )
    }
    this.close = function (id, func) {
        if (!$('#' + id + ' #buttons').length) {
            $('#' + id).append($('<div>').attr('id', 'buttons'))
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
                    EditorMessage.remove(id)
                })
        )
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
}
