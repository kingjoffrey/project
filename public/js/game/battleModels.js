var BattleModels = new function () {
    this.addHero = function (side, i, color, scene) {
        var mesh = Models.getHero(color)

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        mesh.rotation.y = Math.PI
        mesh.scale.x = 0.05
        mesh.scale.y = 0.05
        mesh.scale.z = 0.05

        if (side == 'attack') {
            mesh.position.y = -1
        } else {
            mesh.position.y = 1
        }

        if (i % 2 == 0) {
            mesh.position.x = -i / 2
        } else {
            mesh.position.x = (i + 1) / 2
        }

        // mesh.position.set(0, 0, 0)

        scene.add(mesh)

        return mesh
    }
    this.addUnit = function (side, i, color, modelName, scene) {
        var mesh = Models.getUnit(color, modelName)

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
        }

        mesh.rotation.y = Math.PI
        mesh.scale.x = 0.05
        mesh.scale.y = 0.05
        mesh.scale.z = 0.05

        if (side == 'attack') {
            mesh.position.y = -1
        } else {
            mesh.position.y = 1
        }

        if (i % 2 == 0) {
            mesh.position.x = -i / 2
        } else {
            mesh.position.x = (i + 1) / 2
        }

        // mesh.position.set(0, 0, 0)

        scene.add(mesh)

        return mesh
    }
    this.addCastle = function (castle, color, scene) {
        var mesh = Models.getCastle({name: '', defense: castle.defense}, color)

        mesh.rotation.x = Math.PI / 16

        mesh.scale.x = 0.3
        mesh.scale.y = 0.3
        mesh.scale.z = 0.3

        mesh.children[0].scale.x = 4
        mesh.children[0].scale.y = 4
        mesh.children[0].scale.z = 4

        mesh.position.y = 2
        mesh.position.z = -6

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            mesh.children[0].castShadow = true
        }

        scene.add(mesh)
        return mesh
    }
    this.addTower = function (color, scene) {
        var mesh = Models.getTower(color)
        mesh.scale.x = 0.3
        mesh.scale.y = 0.3
        mesh.scale.z = 0.3

        mesh.children[0].scale.x = 3.3
        mesh.children[0].scale.y = 3.3
        mesh.children[0].scale.z = 3.3

        mesh.position.y = 2
        mesh.position.z = -6

        if (Page.getShadows()) {
            mesh.castShadow = true
            mesh.receiveShadow = true
            mesh.children[0].castShadow = true
        }

        scene.add(mesh)
        return mesh
    }
}
