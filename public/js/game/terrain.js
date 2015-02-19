var Terrain = new function () {
    var terrain
    this.init = function (value) {
        terrain = value
    }
    this.get = function (type) {
        return terrain[type]
    }
    this.toArray = function () {
        return terrain
    }
    this.getName=function(type){
        return terrain[type].name
    }
}