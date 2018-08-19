"use strict"
var WebSocketMessageMapgenerator = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'open':
                // WebSocketEditor.init()
                break

            case 'generated':
                WebSocketSendMain.controller('editor', 'index')
                break

            case 'mirror':
                EditorController.add(r)
                break

            case 'publish':
                GameRenderer.stop()
                $('#game').hide()
                $('#gameMenu').hide()
                WebSocketSendMain.controller('single', 'index')
                break
        }
    }
}
