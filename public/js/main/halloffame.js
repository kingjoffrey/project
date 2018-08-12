"use strict"
var HalloffameController = new function () {
    var halloffameList = ''
    
    this.index = function (r) {
        $('#content').html(r.data)

        halloffameList = $('#halloffameList').html()

        for (var id in r.menu) {
            $('#halloffameMenu')
                .append($('<div>')
                    .attr('id', id)
                    .html(translations[r.menu[id]])
                    .addClass('button buttonColors')
                    .click(function () {
                        Sound.play('click')

                        var id = $(this).attr('id')

                        $('#halloffameMenu div').each(function () {
                            $(this).removeClass('active')
                        })

                        $('#halloffameMenu div#' + id).addClass('active')

                        WebSocketSendMain.controller('halloffame', 'content', {'m': id})
                    })
                )
        }

        WebSocketSendMain.controller('halloffame', 'content', {'m': 3})

        $('#halloffameMenu div#3').addClass('active')
    }

    this.content = function (r) {
        $('#halloffameList').html(halloffameList)

        for (var i in r.data) {

            var place = i * 1 + 1

            $('#halloffameList').append(
                $('<tr>').addClass('trlink').attr('id', r.data[i].playerId)
                    .append($('<td>').html(place + '.'))
                    .append($('<td>').html(r.data[i].firstName + ' ' + r.data[i].lastName))
                    .append($('<td>').html(r.data[i].score))
                    .append($('<td>').html(r.data[i].name))
                    .click(function () {
                        WebSocketSendMain.controller('profile', 'show', {'id': $(this).attr('id')})
                    })
            )
        }

        if (notSet(i)) {
            $('#halloffameList')
                .append(
                    $('<tr>')
                        .append(
                            $('<td colspan="4">').addClass('after').html(translations.Therearenoscorestoshow)
                        )
                )
        }
    }
}