var WebSocketMessageNew = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'start':
                WebSocketSendNew.removeGame(Setup.getGameId())
                GameController.index({'gameId': Setup.getGameId()})
                break;

            case 'update':
                Setup.update(r)
                break

            case 'games':
                Join.addGames(r.games)
                break

            case 'addGame':
                Join.addGame(r.game)
                break

            case 'addPlayer':
                var numberOfPlayersInGame = $('tr#' + r.gameId + ' span').html()
                $('tr#' + r.gameId + ' span').html(numberOfPlayersInGame++)
                break

            case 'removeGame':
                Join.removeGame(r.gameId)
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
