var WebSocketMessageMain = new function () {
    this.switch = function (r) {
        if (r.type == 'open') {
            Main.createMenu(r.menu)
        } else {
            var className = r.type + 'Controller'
            className = capitalizeFirstLetter(className)
            if (typeof window[className] !== "undefined") {
                var methodName = r.action
                if (typeof window[className][methodName] === "function") {
                    // Main.updateMenu(r.type)
                    window[className][methodName](r)

                    $('#back').click(function () {
                        Sound.play('click')
                        WebSocketSendMain.controller('index', 'index')
                    })

                    if (r.type != 'create' && r.type != 'join' && r.type != 'setup' && WebSocketNew.isOpen()) {
                        WebSocketNew.close()
                    }
                    if (r.type != 'help' && HelpRenderer.isRunning()) {
                        HelpRenderer.stop()
                    }
                    if (r.type != 'editor' && WebSocketEditor.isOpen()) {
                        WebSocketEditor.close()
                        GameRenderer.stop()
                        Editor.setInit(0)
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

                    if (r.type == 'index') {
                        $('#menuBox').show()
                    } else {
                        $('#menuBox').hide()
                        $('#content').append(
                            $('<div>').append($('<div>').attr('id', 'back').addClass('button').html(translations.Back))
                        )
                    }
                } else {
                    console.log('Method ' + methodName + ' in class ' + className + ' !exists')
                }
            } else {
                console.log('Class ' + className + ' !exists')
            }
        }
    }
}
