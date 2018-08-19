"use strict"
var HelpController = new function () {
    this.index = function (r) {
        $('#menu').hide()

        $('#content')
            .html(r.data)
            .append($('<div>')
                .append(
                    $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Mainmenu).click(function () {
                        Sound.play('click')
                        IndexController.index()
                        $('#menu').show()
                    })
                ).css({
                    'text-align': 'right'
                })
            )

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
                Help.fillHtml(id.substring(4))
            }))
        }

        $('#helpMenu div').first().addClass('active')
        Help.fillHtml('game')
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