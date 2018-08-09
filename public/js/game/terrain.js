var Terrain = new function () {
    var terrain
    this.init = function (value) {
        terrain = value
    }
    this.get = function (type) {
        if (isSet(terrain[type])) {
            return terrain[type]
        } else {
            console.log('Get terrain type ERROR: ' + type)
        }
    }
    this.toArray = function () {
        return terrain
    }
    this.getName = function (type) {
        return terrain[type].name
    }
}