var ConfigurationWindow = new function () {
    var div = $('<div>').addClass('configuration')
        .append(
            $('<div>').append(
                $('<div>').attr('id', 'show').addClass('iconButton buttonColors').append($('<img>').attr({
                    'src': '/img/game/show.png',
                    'alt': 'Show others'
                })).click(function () {
                    Sound.play('click');
                    show = !show;
                    if (show) {
                        $(this).children().attr('src', '/img/game/show.png')
                    } else {
                        $(this).children().attr('src', '/img/game/show_off.png')
                    }
                })
            ).addClass('row')
        )
        .append(
            $('<div>').append(
                $('<div>').attr('id', 'sound').addClass('iconButton buttonColors').append($('<img>').attr({
                    'src': '/img/game/sound_on.png',
                    'alt': 'Sound'
                })).click(function () {
                    Sound.play('click');
                    Sound.mute = !Sound.mute;
                    if (Sound.mute) {
                        $(this).children().attr('src', '/img/game/sound_off.png')
                    } else {
                        $(this).children().attr('src', '/img/game/sound_on.png')
                    }
                })
            ).addClass('row')
        )
        .append(
            $('<div>').append(
                $('<div>').attr('id', 'fullScreen').addClass('iconButton buttonColors').append($('<img>').attr({
                    'src': '/img/game/full_screen.png',
                    'alt': 'Full screen'
                })).click(function () {
                    var elem = document.getElementById('game');
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen()
                    } else if (elem.msRequestFullscreen) {
                        elem.msRequestFullscreen()
                    } else if (elem.mozRequestFullScreen) {
                        elem.mozRequestFullScreen()
                    } else if (elem.webkitRequestFullscreen) {
                        elem.webkitRequestFullscreen()
                    }
                })
            ).addClass('row')
        )

    this.show = function () {
        var id = Message.simple(translations.configuration, div)
        Message.setOverflowHeight(id)
    }
}
