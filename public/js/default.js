$(document).ready(function () {
    Page.init()
    Chat.init()
})

var Page = new function () {
    var index = '',
        touch = 0,
        shadows = 1


    this.getIndex = function () {
        return index
    }
    this.getShadows = function () {
        return shadows
    }
    this.setShadows = function (s) {
        shadows = s
    }
    this.hasTouch = function () {
        return touch
    }
    this.fullScreen = function () {
        var elem = document.getElementById('main');
        if (elem.requestFullscreen) {
            elem.requestFullscreen()
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen()
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen()
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen()
        }
    }

    this.init = function () {
        $('#bg').scroll(function () {
            var x = $(this).scrollTop();
            $(this).css('background-position', '0% ' + parseInt(-x / 10) + 'px');
        })

        if (!index) {
            index = $('#content').html()
        }

        if (isSet(window.orientation)) {
            $('body').addClass('touchscreen')
            touch = 'ontouchstart' in document.documentElement
            shadows = 0
        }

        if ($(window).innerWidth() < $(window).innerHeight()) {
            $('body').addClass('vertical')
        }

        $('#menuBox').append($('<div>').addClass('askFullScreen')
            .append($('<div>').html(translations.SwitchtoFullScreen).addClass('question'))
            .append(
                $('<div>')
                    .append($('<div>').addClass('button buttonColors').html(translations.No).click(function () {
                        Sound.play('click')
                        $('.askFullScreen').remove()
                    }))
                    .append($('<div>').addClass('button buttonColors').html(translations.Yes).click(function () {
                        Sound.play('click')
                        Page.fullScreen()
                        $('.askFullScreen').remove()
                    }))
            )
        )
    }
}
