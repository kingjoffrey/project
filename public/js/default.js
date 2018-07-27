var Page = new function () {
    var index = '',
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
    this.fullScreen = function () {
        var isInFullScreen = (document.fullscreenElement && document.fullscreenElement !== null) ||
            (document.webkitFullscreenElement && document.webkitFullscreenElement !== null) ||
            (document.mozFullScreenElement && document.mozFullScreenElement !== null) ||
            (document.msFullscreenElement && document.msFullscreenElement !== null);

        var docElm = document.documentElement
        if (!isInFullScreen) {
            $('#fullScreen div').addClass('full')

            if (docElm.requestFullscreen) {
                docElm.requestFullscreen();
            } else if (docElm.mozRequestFullScreen) {
                docElm.mozRequestFullScreen();
            } else if (docElm.webkitRequestFullScreen) {
                docElm.webkitRequestFullScreen();
            } else if (docElm.msRequestFullscreen) {
                docElm.msRequestFullscreen();
            }
        } else {
            $('#fullScreen div.full').removeClass('full')

            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }

    this.init = function () {
        $('#lang').change(function () {
            window.location = '/' + $(this).val() + '/login'
        })

        var url_parts = window.location.pathname.split('/')

        $('#lang option').each(function () {
            if (url_parts[1] == $(this).val()) {
                $(this).attr('selected', '')
            }
        })

        $('#bg').scroll(function () {
            var x = $(this).scrollTop();
            $(this).css('background-position', '0% ' + parseInt(-x / 10) + 'px');
        })

        if (!index) {
            index = $('#content').html()
        }

        // console.log(window.orientation)
        // console.log(isTouchDevice())

        if (isTouchDevice()) {
            $('body').addClass('touchscreen')
            shadows = 0
        }

        if ($(window).innerWidth() < $(window).innerHeight()) {
            $('body').addClass('vertical')
        }

        if (!$('#menuBox #menu').length) {
            return
        }

        Chat.init()
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

$(document).ready(function () {
    Page.init()
})
$(window).resize(function () {
    if ($(window).innerWidth() < $(window).innerHeight()) {
        $('body').addClass('vertical')
    } else {
        $('body').removeClass('vertical')
    }
})
