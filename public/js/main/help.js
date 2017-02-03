"use strict"
var HelpController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data

        content.html(data)

        Help.init(r)

        var width = 300,
            height = 300,
            menu = r.menu


        HelpScene.init(width, height)
        HelpRenderer.init(HelpScene, width, height)
        HelpScene.initSun(40)
        HelpModels.init()

        for (var id in menu) {
            $('#helpMenu').append($('<div>').attr('id', 'help' + id).html(menu[id]).addClass('button').click(function () {
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
        $('.table').css({'min-height': '300px'})
    }
}