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
                    window[className][methodName](r)

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

                    if (r.type == 'create' && r.action == 'map') {
                        return
                    } else {
                        $('#menuBox').hide()
                        if (r.type == 'help') {
                            $('#content')
                                .append($('<div>')
                                    .append(
                                        $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Back).click(function () {
                                            Sound.play('click')
                                            IndexController.index({'data': Page.getIndex()})
                                        })
                                    ).css({
                                        'text-align': 'right'
                                    })
                                )
                        } else {
                            $('#content')
                                .prepend($('<div>')
                                    .append(
                                        $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Back).click(function () {
                                            Sound.play('click')
                                            IndexController.index({'data': Page.getIndex()})
                                        })
                                    ).css({
                                        'text-align': 'right'
                                    })
                                )
                                .append($('<div>')
                                    .append(
                                        $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Back).click(function () {
                                            Sound.play('click')
                                            IndexController.index({'data': Page.getIndex()})
                                        })
                                    ).css({
                                        'text-align': 'right'
                                    })
                                )
                        }
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
