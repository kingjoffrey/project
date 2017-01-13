var MapGenerator = new function () {
    var fields = [],
        mapSize = 32

    this.getFields = function () {
        return fields
    }
    this.init = function () {
        for (var y = 0; y < mapSize; y++) {
            if (notSet(fields[y])) {
                fields[y] = []
            }
            for (var x = 0; x < mapSize; x++) {
                fields[y][x] = 'g'
            }
        }
        WebSocketMapgenerator.init()
    }
}
