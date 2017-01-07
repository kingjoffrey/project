var WebSocketMessageMain = new function () {
    this.switch = function (r) {
        if (r.type == 'open') {
            delete r.type
            Main.createMenu(r)
        } else {
            var className = r.type + 'Controller'
            className = capitalizeFirstLetter(className)
            if (typeof window[className] !== "undefined") {
                var methodName = r.action
                if (typeof window[className][methodName] === "function") {
                    Main.updateMenu(r.type)
                    window[className][methodName](r)
                    if (r.type != 'new' && WebSocketNew.isOpen()) {
                        WebSocketNew.close()
                    }
                    if (r.type != 'help' && HelpRenderer.isRunning()) {
                        HelpRenderer.stop()
                    }
                    if (r.type != 'editor' && WebSocketEditor.isOpen()) {
                        WebSocketEditor.close()
                        GameRenderer.stop()
                    }
                    if (r.type != 'game') {
                        if (WebSocketGame.isOpen()) {
                            WebSocketGame.close()
                            WebSocketExecGame.close()
                            GameRenderer.stop()
                            Game.resetLoading()
                        }
                        if (WebSocketTutorial.isOpen()) {
                            WebSocketTutorial.close()
                            WebSocketExecTutorial.close()
                            GameRenderer.stop()
                            Game.resetLoading()
                        }
                    }
                }
            }
        }
    }
}
