var EditorWS = {
    save: function () {
        var token = {
            type: 'save',
            pixels: Editor.pixels
        }

        ws.send(JSON.stringify(token));
    }
}
