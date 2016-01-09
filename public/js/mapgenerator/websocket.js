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
                accessKey: accessKey
            }

            ws.send(JSON.stringify(token));
        }
    this.save = function () {
        var token = {
            type: 'save',
            mapId: mapId,
            map: MapGenerator.getImage(),
            fields: MapGenerator.getFields()
        }

        ws.send(JSON.stringify(token))

        window.location = '/' + lang + '/editor/edit/mapId/' + mapId
    }

    this.init = function () {
        ws = new WebSocket(wsURL + '/editor')

        ws.onopen = function () {
            closed = false
            open()

            if (!MapGenerator.getInit()) {
                MapGenerator.init(mapSize)
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
