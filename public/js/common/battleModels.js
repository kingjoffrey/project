var BattleModels = new function () {
    this.addHero = function (side, i, color, scene) {
        var mesh = Models.getHero(color)

        if (scene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 10
        mesh.scale.y = 10
        mesh.scale.z = 10
        mesh.position.set(0, -40, 0)

        scene.add(mesh)
    }
    this.addUnit = function (side, i, color, modelName, scene) {
        var mesh = Models.getUnit(color, modelName)

        if (scene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 5
        mesh.scale.y = 5
        mesh.scale.z = 5
        mesh.position.set(0, -40, 0)

        scene.add(mesh)
    }
}
