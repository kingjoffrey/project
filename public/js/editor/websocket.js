var WebSocketEditor = new function () {
    var closed = true,
        ws,
        onMessage = function (r) {
            //console.log(r)
            if (!Editor.getInit()) {
                Editor.init(r['fields'])
            }
        },
        open = function () {
            if (closed) {
                console.log(translations.sorryServerIsDisconnected)
                return;
            }

            var token = {
                type: 'open',
                playerId: id,
                mapId: mapId,
                accessKey: accessKey
            }

            ws.send(JSON.stringify(token));
        }

    this.init = function () {
        ws = new WebSocket(wsURL + '/editor')

        ws.onopen = function () {
            closed = false
            open()
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
