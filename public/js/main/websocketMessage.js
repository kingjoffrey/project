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

                    if ($('#back').length) {
                        return
                    } else {
                        $('#menuBox').hide()
                        if (r.type == 'help') {
                            $('#content')
                                .append($('<div>')
                                    .append(
                                        $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Back).click(function () {
                                            Sound.play('click')
                                            IndexController.index()
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
                                            IndexController.index()
                                        })
                                    ).css({
                                        'text-align': 'right'
                                    })
                                )
                                .append($('<div>')
                                    .append(
                                        $('<div>').attr('id', 'back').addClass('button buttonColors').html(translations.Back).click(function () {
                                            Sound.play('click')
                                            IndexController.index()
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
