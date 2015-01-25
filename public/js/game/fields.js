var Fields = new function () {
    var fields
    this.init = function (fields) {

    }
    this.add = function (x, y, field) {
        fields[y][x] = new Field(field)
    }
    this.get = function () {
        return fields[y][x]
    }
}