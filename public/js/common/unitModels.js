var UnitModels = new function () {
    this.addHero = function (color) {
        var mesh = Models.getHero(color)

        mesh.position.set(20, 0, -20)

        mesh.scale.x = 1.5
        mesh.scale.y = 1.5
        mesh.scale.z = 1.5

        if (UnitScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        UnitScene.add(mesh)
        return mesh
    }
    this.addUnit = function (color, modelName) {
        var mesh = Models.getUnit(color, modelName)
        //mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 0.5
        mesh.scale.y = 0.5
        mesh.scale.z = 0.5
        mesh.position.set(20, 0, -20)

        if (UnitScene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        UnitScene.add(mesh)
        return mesh
    }
}
