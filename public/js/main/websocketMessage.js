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
                }
            }
        }
    }
}