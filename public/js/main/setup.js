"use strict"
var SetupController = new function () {
    this.index = function (r) {
        var content = $('#content'),
            data = r.data,
            mapPlayers = r.mapPlayers,
            numberOfMapPlayers = 0

        content.html(data)

        for (var id in mapPlayers) {
            numberOfMapPlayers++
            $('#playersingame').append($('<tr>').attr('id', id)
                .append($('<td>').addClass('td1').html($('<div>').addClass('colorBox').css('background', mapPlayers[id].backgroundColor)))
                .append($('<td>').addClass('td2')
                    .append($('<a>').addClass('button buttonColors').html(translations.select).attr('id', id).click(function () {
                        WebSocketSendNew.change(this.id)
                    }))
                )
                .append($('<td>').addClass('td3'))
                .append($('<td>').addClass('td4').html($('<div>').addClass('colorBox').css('background', mapPlayers[id].backgroundColor)))
            )
        }

        WebSocketSendNew.setup(r.gameId)

        Setup.init(r.gameId, r.gameMasterId, numberOfMapPlayers)
    }
}