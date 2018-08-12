"use strict"
var HalloffameController = new function () {
    var playerId
    this.index = function (r) {
        $('#content').html(r.data)

        for (var id in r.menu) {
            $('#halloffameMenu')
                .append($('<div>')
                    .attr('id', 'halloffame' + id)
                    .html(translations[r.menu[id]])
                    .addClass('button buttonColors')
                    .click(function () {
                        Sound.play('click')

                        var id = $(this).attr('id')

                        $('#halloffameMenu div').each(function () {
                            $(this).removeClass('active')
                        })

                        $('#halloffameMenu div#' + id).addClass('active')

                        WebSocketSendMain.controller('halloffame', 'content', {'m': id.substring(id.length - 1)})
                    })
                )
        }

        // $('#helpMenu div').first().addClass('active')
    }

    this.content = function (r) {

        console.log(r.data)

        $('.trlink').click(function () {
            playerId = $(this).attr('id')
            WebSocketSendMain.controller('profile', 'show', {'id': playerId})
        })
    }
}