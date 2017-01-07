"use strict"
var WebSocketSendMapgenerator = new function () {
    var ws

    this.open = function () {
        var token = {
            type: 'open',
            playerId: id,
            accessKey: accessKey,
            mapId: EditorController.getMapId(),
            map: MapGenerator.getImage(),
            fields: MapGenerator.getFields()
        }

        ws.send(JSON.stringify(token))
    }
    this.init = function (param) {
        ws = param
    }
}
