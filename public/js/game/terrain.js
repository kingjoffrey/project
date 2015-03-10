var Terrain = new function () {
    var terrain
    this.init = function (value) {
        console.log(value)
        terrain = value
    }
    this.get = function (type) {
        if (isSet(terrain[type])) {
            return terrain[type]
        } else {
            console.log('type: ' + type)
        }
    }
    this.toArray = function () {
        return terrain
    }
    this.getName = function (type) {
        return terrain[type].name
    }
}