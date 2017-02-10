"use strict"
var HelpController = new function () {
    this.index = function (r) {
        $('#content').html(r.data)

        Help.init(r)

        var width = $(window).innerWidth() / 10 * 3,
            height = width,
            menu = r.menu


        HelpScene.init(width, height)
        HelpRenderer.init(HelpScene, width, height)
        HelpScene.initSun(40)
        HelpModels.init()

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

        $(window).resize(function () {
            var x = $(window).innerWidth() / 10 * 3
            HelpScene.resize(x, x)
            HelpRenderer.resize(x, x)
        })
    }
}