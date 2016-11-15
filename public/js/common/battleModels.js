var BattleModels = new function () {
    this.addHero = function (side, i, color, scene) {
        var mesh = Models.getHero(color)

        if (scene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 0.1
        mesh.scale.y = 0.1
        mesh.scale.z = 0.1

        if (side == 'attack') {
            mesh.position.y = -1
        } else {
            mesh.position.y = 1
        }
        // mesh.position.set(0, 0, 0)

        scene.add(mesh)

        return mesh
    }
    this.addUnit = function (side, i, color, modelName, scene) {
        var mesh = Models.getUnit(color, modelName)

        if (scene.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        mesh.rotation.y = Math.PI / 2
        mesh.scale.x = 0.2
        mesh.scale.y = 0.2
        mesh.scale.z = 0.2

        if (side == 'attack') {
            mesh.position.y = -1
        } else {
            mesh.position.y = 1
        }
        // mesh.position.set(0, 0, 0)

        scene.add(mesh)

        return mesh
    }
}
