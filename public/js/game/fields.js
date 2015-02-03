var Fields = new function () {
    var fields = {}
    this.init = function (fields) {
        for (var y in fields) {
            for (var x in fields[y]) {
                switch (fields[y][x].type) {
                    case 'm':
                        //Three.addMountain(x, y)
                        break
                    case 'h':
                        //Three.addHill(x, y)
                        break
                    case 'f':
                        //Three.addTree(x, y)
                        break
                    //case 'w':
                    //    Three.addWater(x, y)
                        break
                }
                this.add(x, y, fields[y][x])
            }
        }
    }
    this.add = function (x, y, field) {
        if (typeof fields[y] == 'undefined') {
            fields[y] = {}
        }
        fields[y][x] = new Field(field)
    }
    this.get = function () {
        return fields[y][x]
    }
}