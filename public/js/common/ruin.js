var Ruin = function (ruin) {
    this.update = function (empty) {
        ruin.empty = empty
        mesh.material.color.set(this.getColor())
    }
    this.getColor = function () {
        ruin.type = ruin.type * 1
        switch (ruin.type) {
            case 1:
                return '#FF0000'
                break
            case 2:
                return '#0000FF'
                break
            case 3:
                return '#089000'
                break
            case 4:
                if (ruin.empty) {
                    return '#8080a0'
                } else {
                    return '#FFD700'
                }
                break
            default:
                console.log('dupa bladaa')
                console.log(ruin.type)
                return '#8080a0'
        }
    }
    this.getX = function () {
        return ruin.x
    }
    this.getY = function () {
        return ruin.y
    }
    this.getMesh = function () {
        return mesh
    }
    this.isRandom = function () {
        if (ruin.type == 4) {
            return 1
        } else {
            return 0
        }
    }
    this.isEmpty = function () {
        return ruin.empty
    }
    this.setType = function (type) {
        if (ruin.type != type) {
            ruin.type = type
            mesh.material.color.set(this.getColor())
        }
    }
    this.getType = function () {
        return ruin.type
    }

    var mesh = GameModels.addRuin(ruin.x, ruin.y, this.getColor())
}
