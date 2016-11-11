var UnitModels = new function () {
    this.addHero = function (color, scene) {
        var mesh = Models.getHero(color)

        // mesh.position.set(20, 0, -20)
        // mesh.scale.x = 1.5
        // mesh.scale.y = 1.5
        // mesh.scale.z = 1.5

        if (scene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        // var mesh = Models.getHero(4, 4, army.getBackgroundColor())
        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 10
        mesh.scale.y = 10
        mesh.scale.z = 10
        mesh.position.set(0, -40, 0)

        scene.add(mesh)
    }
    this.addUnit = function (color, modelName, scene) {
        console.log(color)
        console.log(modelName)
        var mesh = Models.getUnit(color, modelName)
        //mesh.rotation.y = Math.PI / 2 + Math.PI / 4
        // mesh.rotation.y = Math.PI / 2
        // mesh.scale.x = 0.5
        // mesh.scale.y = 0.5
        // mesh.scale.z = 0.5
        // mesh.position.set(20, 0, -20)

        if (scene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        // var mesh = Models.addUnit(army.getBackgroundColor(), Unit.getName(unitId))
        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 5
        mesh.scale.y = 5
        mesh.scale.z = 5
        mesh.position.set(0, -40, 0)

        scene.add(mesh)
    }
}
