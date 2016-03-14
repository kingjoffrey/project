var MessageQueue = new function () {
    var queue = {},
        i = 0

    this.addQueue = function (simple) {
        i++
        queue[i] = simple
    }
}