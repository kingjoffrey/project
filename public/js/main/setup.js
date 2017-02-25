"use strict"
var SetupController = new function () {
    this.index = function (r) {
        var numberOfMapPlayers = 0

        $('#content').html(r.data)

        for (var i in r.teams) {
            var team = r.teams[i],
                sides = team.sides

            for (var j in sides) {
                var side = sides[j]
                numberOfMapPlayers++

                $('#' + i + '.playersingame').append($('<tr>').attr('id', side.sideId)
                    .append($('<td>').addClass('td1').html($('<div>').addClass('colorBox').css('background', side.backgroundColor)))
                    .append($('<td>').addClass('td2')
                        .append($('<a>').addClass('button buttonColors').html(translations.select).attr('id', side.sideId).click(function () {
                            if ($(this).hasClass('buttonOff')) {
                                return
                            }
                            WebSocketSendNew.change(this.id)
                        }))
                    )
                    .append($('<td>').addClass('td3'))
                )
            }

        }

        WebSocketSendNew.setup(r.gameId)

        Setup.init(r.gameId, r.gameMasterId, numberOfMapPlayers)
    }
}