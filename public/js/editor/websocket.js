var EditorWS = {
    save: function () {
        var token = {
            type: 'save',
            mapId: mapId,
            map: Editor.pixelCanvas.toDataURL('image/png')
        }

        ws.send(JSON.stringify(token));
    }
}
