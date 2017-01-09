var MapGenerator = new function () {
    var fields = []

    this.getFields = function () {
        return fields
    }
    this.init = function (mapSize) {
        for (var y = 0; y < mapSize - 1; y++) {
            if (notSet(fields[y])) {
                fields[y] = []
            }
            for (var x = 0; x < mapSize - 1; x++) {
                fields[y][x] = 'g'
            }
        }
        WebSocketMapgenerator.init()
    }
}
