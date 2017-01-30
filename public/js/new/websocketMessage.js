var WebSocketMessageNew = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'start':
                WebSocketSendNew.removeGame(New.getGameId())
                WebSocketSendMain.controller('game', 'index', {'gameId': New.getGameId()})
                break;

            case 'update':
                if (notSet(r.close)) {
                    New.removePlayer(r.player.playerId)
                    if (r.player.mapPlayerId) {
                        $('#' + r.player.mapPlayerId + ' .td3').html(r.player.firstName + ' ' + r.player.lastName)

                        if (r.player.playerId == id) {
                            $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.deselect)
                            $('#' + r.player.mapPlayerId).addClass('selected')
                        } else {
                            if (New.getGameMasterId() == id) {
                                $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.deselect);
                            } else {
                                $('#' + r.player.mapPlayerId + ' .td2 a').remove();
                            }
                        }
                        $('#' + r.player.mapPlayerId + ' .td1').attr('id', r.player.playerId)
                    } else {
                        $('#playersout').append(
                            $('<tr>')
                                .html($('<td>').html(r.player.firstName + ' ' + r.player.lastName))
                                .attr('id', r.player.playerId)
                        )
                    }
                    New.updateStartButton()
                } else {
                    WebSocketSendMain.controller('new', 'index')
                }
                break;

            case 'games':
                New.addGames(r.games)
                WebSocketSendMain.controller('new', 'map', {'mapId': $('#mapId').val()})
                break
            case 'addGame':
                New.addGame(r.game)
                break
            case 'addPlayer':
                var numberOfPlayersInGame = $('tr#' + r.gameId + ' span').html()
                $('tr#' + r.gameId + ' span').html(numberOfPlayersInGame++)
                break
            case 'removeGame':
                New.removeGame(r.gameId)
                break;
            case 'removePlayer':
                var numberOfPlayersInGame = $('tr#' + r.gameId + ' span').html()
                $('tr#' + r.gameId + ' span').html(numberOfPlayersInGame--)
                break
            default:
                console.log(r)
        }
    }
}
