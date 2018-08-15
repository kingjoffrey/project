"use strict"
var WebSocketMessageMapgenerator = new function () {
    this.switch = function (r) {
        console.log(r)
        switch (r.type) {
            case 'open':
                // WebSocketEditor.init()
                break

            case 'generated':
                EditorController.add(r)
                break
        }
    }
}
