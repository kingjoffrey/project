var Message = new function () {
    var maxHeight

    this.info = function (title, message) {
        var id = this.show(title, $('<div>').html(message).addClass('simple'), 1)
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
    this.show = function (title, txt, info) {
        this.remove()
        var id = makeId(10),
            div = $('<div>')
                .addClass('message')
                .attr('id', id)
                .append($('<div>').attr('id', 'content')
                    .append($('<div>').append($('<h3>').html(title)).addClass('msgTitle'))
                    .append($(txt).addClass('overflow'))
                )
                .fadeIn(200)
        if (isSet(info)) {
            div.addClass('info')
        }
        $('#goldBox').after(div)
        this.adjust(id)
        return id
    }
    this.remove = function (id) {
        if (isSet(id)) {
            $('#' + id).fadeOut(200, function () {
                $('#' + id).remove()
            })
        } else {
            if (!Turn.isMy() && $('.message .showCastle').length) {
                $('.message:not(:has(.showCastle))').remove()
            } else {
                $('.message:not(.info)').remove()
            }
        }
    }
    this.adjust = function (id) {
        maxHeight = Scene.getHeight() - 140

        if (maxHeight < parseInt($('#' + id + ' #content').css('min-height'))) {
            maxHeight = parseInt($('#' + id + ' #content').css('min-height'))
        }

        if ($('#' + id + ' .showCastle').length) {
            $('#' + id + ' #content').css({
                'z-index': $('#' + id).css('z-index') + 1,
                'max-height': maxHeight + 'px'
            })
        } else if ($('#' + id).length) {
            $('#' + id + ' #content').css({
                'max-height': maxHeight + 'px'
            })
        } else {
            $('.message #content').css({
                'max-height': maxHeight + 'px'
            })
        }
    }
    this.setOverflowHeight = function (id) {
        if ($('#' + id + ' .showCastle').length) {
            var minus = -20
        } else if ($('#' + id + ' #content').height() == maxHeight) {
            var minus = 65
        } else {
            var minus = 20
        }

        var height = $('#' + id + ' #content').height() - minus;

        $('#' + id + ' div.overflow').css('height', height + 'px')

        if (CommonMe.isSelected()) {
            CommonMe.setIsSelected(0)
        }
    }
    this.ok = function (id, func) {
        if (!$('#' + id + ' #buttons').length) {
            $('#' + id + ' #content').append($('<div>').attr('id', 'buttons'))
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
        );

        this.setOverflowHeight(id)
    }
    this.cancel = function (id, func) {
        if (!$('#' + id + ' #buttons').length) {
            $('#' + id + ' #content').append($('<div>').attr('id', 'buttons'))
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
    }
    this.close = function (id, func) {
        if (!$('#' + id + ' #buttons').length) {
            $('#' + id + ' #content').append($('<div>').attr('id', 'buttons'))
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
    }
}
