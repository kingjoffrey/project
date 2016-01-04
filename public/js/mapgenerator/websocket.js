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
    this.save = function () {
        /*
         * since the stage toDataURL() method is asynchronous, we need
         * to provide a callback
         */
//        stage.toDataURL({
//          callback: function(dataUrl) {
        /*
         * here you can do anything you like with the data url.
         * In this tutorial we'll just open the url with the browser
         * so that you can see the result as an image
         */
//            window.open(dataUrl);
//          }
//        })

        var token = {
            type: 'save',
            mapId: mapId,
            map: MapGenerator.getImage()
        }

        ws.send(JSON.stringify(token))
    }

    this.init = function () {
        ws = new WebSocket(wsURL + '/editor')

        ws.onopen = function () {
            closed = false
            open()

            if (!MapGenerator.getInit()) {
                MapGenerator.init()
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
