"use strict"
var HelpController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        $('.table').css({
            'padding': 0
        })

        Renderer.clear()

        Help.init(r)

        var width = $(window).innerWidth() / 10 * 3,
            menu = r.menu


        for (var id in menu) {
            $('#helpMenu').append($('<div>').attr('id', 'help' + id).html(menu[id]).addClass('button buttonColors').click(function () {
                Sound.play('click')

                var id = $(this).attr('id')

                $('#helpMenu div').each(function () {
                    $(this).removeClass('active')
                })

                $('#helpMenu div#' + id).addClass('active')
                Help.fillText(id.substring(4))
            }))
        }

        $('#helpMenu div').first().addClass('active')
        Help.fillText('game')
        $('.table').css({'min-height': '30vw'})

        HelpScene.resize()
        HelpRenderer.resize(width)

        $(window).resize(function () {
            var x = $(window).innerWidth() / 10 * 3
            HelpScene.resize()
            HelpRenderer.resize(x)
        })
    }
}