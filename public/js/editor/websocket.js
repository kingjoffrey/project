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
            map: Editor.pixelCanvas.toDataURL('image/png')
        }

        ws.send(JSON.stringify(token))
    }
    this.castleAdd = function (x, y) {
        var token = {
            type: 'castleAdd',
            mapId: mapId,
            x: x,
            y: y
        }

        ws.send(JSON.stringify(token))
    }
    this.castleRemove = function (x, y) {
        var token = {
            type: 'castleRemove',
            mapId: mapId,
            x: x,
            y: y
        }

        ws.send(JSON.stringify(token))
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
