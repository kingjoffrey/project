var Message = new function () {
    var element = $('#goldBox')

    this.remove = function (id) {
        if (isSet(id)) {
            $('#' + id).fadeOut(200, function () {
                this.remove();
            })
        } else {
            if (notSet($('.message'))) {
                return;
            }
            $('.message').remove();
        }
    }
    this.show = function (title, txt) {
        this.remove()
        var id = makeId(10)
        element().after(
            $('<div>')
                .addClass('message box')
                .append($('<h3>').html(title).addClass('msgTitle'))
                .append($(txt).addClass('overflow'))
                .attr('id', id)
                .fadeIn(200)
        )
        this.adjust(id)
        return id
    }
    this.adjust = function (id) {
        var maxHeight = Three.getHeight() - 140,
            maxWidth = Three.getWidth() - 480

        if (isSet(id)) {
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

            if (left < $('#mapBox').width() + 30) {
                left = $('#mapBox').width() + 30
            }

            $('#' + id).css({
                left: left + 'px'
            })
        } else if ($('.message').length) {
            if (maxHeight < parseInt($('.message').css('min-height'))) {
                maxHeight = parseInt($('.message').css('min-height'))
            }
            if (maxWidth < parseInt($('.message').css('min-width'))) {
                maxWidth = parseInt($('.message').css('min-width'))
            }

            $('.message').css({
                'max-width': maxWidth + 'px',
                'max-height': maxHeight + 'px'
            })

            var left = Three.getWidth() / 2 - $('.message').outerWidth() / 2;

            if (left < $('#mapBox').width() + 30) {
                left = $('#mapBox').width() + 30
            }

            $('.message').css({
                left: left + 'px'
            })
        }
    }
    this.setOverflowHeight = function (id) {
        var minus = 0
        if (isSet(id)) {
            var height = $('#' + id).height() - minus;
            $('#' + id + ' div.overflow').css('height', height + 'px')
        } else {
            var height = $('.message').height() - minus;
            $('.message' + ' div.overflow').css('height', height + 'px')
        }
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
