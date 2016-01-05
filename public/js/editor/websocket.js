var WebSocketEditor = new function () {
    var closed = true,
        ws,
        onMessage = function (r) {
            console.log(r)
        },
        open = function () {
            if (closed) {
                console.log(translations.sorryServerIsDisconnected)
                return;
            }

            var token = {
                type: 'open',
                playerId: id,
                name: playerName,
                langId: langId,
                accessKey: accessKey
            }

            ws.send(JSON.stringify(token));
        }

    this.init = function () {
        ws = new WebSocket(wsURL + '/editor')

        ws.onopen = function () {
            closed = false
            open()

            if (!Editor.getInit()) {
                Editor.init()
            }
        }
        ws.onmessage = function (e) {
            onMessage($.parseJSON(e.data))
        }
        ws.onclose = function () {
            closed = true;
            setTimeout('WebSocketEditor.init()', 1000)
        }
    }
}
