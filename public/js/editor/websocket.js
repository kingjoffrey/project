var EditorWS = {
    save: function () {
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
    },
    castleAdd: function (x, y) {
        var token = {
            type: 'castleAdd',
            mapId: mapId,
            x: x,
            y: y
        }

        ws.send(JSON.stringify(token))
    },
    castleRemove: function (x, y) {
        var token = {
            type: 'castleRemove',
            mapId: mapId,
            x: x,
            y: y
        }

        ws.send(JSON.stringify(token))
    }
}
