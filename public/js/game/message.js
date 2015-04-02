var Message = new function () {
    var max = false,
        maxHeight,
        maxWidth

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
        $('#goldBox').after(
            $('<div>')
                .addClass('message box')
                .attr('id', id)
                .append($('<h3>').html(title).addClass('msgTitle'))
                .append($(txt).addClass('overflow'))
                .fadeIn(200)
        )
        this.adjust(id)
        return id
    }
    this.adjust = function (id) {
        maxHeight = Three.getHeight() - 140
        maxWidth = Three.getWidth() - 40

        if (maxHeight < parseInt($('#' + id).css('min-height'))) {
            maxHeight = parseInt($('#' + id).css('min-height'))

        }
        if (maxWidth < parseInt($('#' + id).css('min-width'))) {
            maxWidth = parseInt($('#' + id).css('min-width'))
        }

        $('#' + id).css({
            'max-width': maxWidth + 'px',
            'max-height': maxHeight + 'px'
        })

        var left = Three.getWidth() / 2 - $('#' + id).outerWidth() / 2

        $('#' + id).css({
            left: left + 'px'
        })
    }
    this.setOverflowHeight = function (id) {
        if ($('#' + id + ' .showCastle').length) {
            var minus = 0
        } else if ($('#' + id).height() == maxHeight) {
            var minus = 65
        } else {
            var minus = 20
        }

        var height = $('#' + id).height() - minus;

        $('#' + id + ' div.overflow').css('height', height + 'px')

        if (Me.isSelected()) {
            Me.setIsSelected(0)
        }
    }
    this.ok = function (id, func) {
        $('#' + id).append(
            $('<div>')
                .addClass('button buttonColors go')
                .html(translations.ok)
                .click(function () {
                    if (isSet(func)) {
                        func();
                    }
                    Message.remove(id);
                })
        );

        this.setOverflowHeight(id)
    }
    this.cancel = function (id, func) {
        $('#' + id).append(
            $('<div>')
                .addClass('button buttonColors cancel')
                .html(translations.cancel)
                .click(function () {
                    if (isSet(func)) {
                        func();
                    }
                    Message.remove(id);
                })
        )
    }
    this.simple = function (title, message) {
        var id = this.show(title, $('<div>').html(message).addClass('simple'));
        this.ok(id)
        return id
    }
    this.error = function (message) {
        Sound.play('error');
        var div = $('<div>').html(message).addClass('error')
        this.simple(translations.error, div);
    }
}
