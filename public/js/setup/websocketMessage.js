var WebSocketMessage = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'chat':
                PrivateChat.message(2, r.name, r.id, r.msg)
                break

            case 'open':
                WebSocketNew.init()
                break

            case 'team':
                $('tr#' + r.mapPlayerId + ' select').val(r.teamId)
                $('tr#' + r.mapPlayerId + ' .td4 img').attr('src', '/img/game/heroes/' + mapPlayers[r.teamId].shortName + '.png')
                break

            case 'start':
                WebSocketSendNew.removeGame(gameId)
                top.location.replace('/' + lang + '/game/index/id/' + gameId)
                break;

            case 'update':
                Setup.setGameMasterId(r.gameMasterId)
                Setup.removePlayer(r.player.playerId)

                if (notSet(r.close)) {
                    if (r.player.mapPlayerId) {
                        $('#' + r.player.mapPlayerId + ' .td3').html(r.player.firstName + ' ' + r.player.lastName)

                        if (r.player.playerId == id) {
                            $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.deselect)
                            $('#' + r.player.mapPlayerId).addClass('selected')
                        } else {
                            if (r.gameMasterId == id) {
                                $('#' + r.player.mapPlayerId + ' .td2 a').html(translations.deselect);
                            } else {
                                $('#' + r.player.mapPlayerId + ' .td2 a').remove();
                            }
                        }
                        $('#' + r.player.mapPlayerId + ' .td1').attr('id', r.player.playerId)
                    } else {
                        Setup.getPlayersOutElement().append(
                            $('<tr>')
                                .html($('<td>').html(r.player.firstName + ' ' + r.player.lastName))
                                .attr('id', r.player.playerId)
                        )
                    }
                }
                Setup.updateStartButton()
                break;

            default:
                console.log(r)
        }
    }
}
