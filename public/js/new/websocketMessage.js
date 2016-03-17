var WebSocketMessageNew = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'games':
                New.addGames(r.games)
                WebSocketSendNew.map($('#mapId').val())
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
            case 'open':
                PrivateChat.message(2, r.name, r.id, translations.connected)
                break
            case 'close':
                PrivateChat.message(2, r.name, r.id, translations.disconnected)
                break
            case 'chat':
                PrivateChat.message(2, r.name, r.id, r.msg)
                break
            case 'map':
                $('#x').val(r.number)
                $('#numberOfPlayers').val(r.number)
                New.changeMap(r.fields)
                break;
            default:
                console.log(r)
        }
    }
}
