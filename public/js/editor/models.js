var EditorModels = new function () {
    this.createMesh = function (type) {
        switch (type) {
            case 'castle':
                var castleMaterial = new THREE.MeshLambertMaterial({color: '#3B3028', side: THREE.DoubleSide}),
                    mesh = new THREE.Mesh(castleModel_1.geometry, castleMaterial)
                break
            case 'ruin':
                var ruinMaterial = new THREE.MeshPhongMaterial({color: '#FFD700', side: THREE.DoubleSide}),
                    mesh = new THREE.Mesh(ruinModel.geometry, ruinMaterial)
                break
            case 'tower':
                var towerMaterial = new THREE.MeshLambertMaterial({color: '#6B6B6B', side: THREE.DoubleSide}),
                    mesh = new THREE.Mesh(towerModel.geometry, towerMaterial)
                break
            case 'road':
                var roadMaterial = new THREE.MeshLambertMaterial({
                        map: roadTexture.p,
                        side: THREE.DoubleSide,
                        transparent: true,
                        opacity: 0.5
                    }),
                    mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), roadMaterial)
                mesh.rotation.x = Math.PI / 2
                break
            case 'bridge':

                break
            case 'forest':
                var mesh = new THREE.Mesh(treeModel.geometry, treeModel.material)
                break
            case 'swamp':
                var swampMaterial = new THREE.MeshLambertMaterial({
                        map: swampTexture,
                        side: THREE.DoubleSide,
                        transparent: true,
                        opacity: 0.5
                    }),
                    mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), swampMaterial)
                mesh.rotation.x = Math.PI / 2
                mesh.position.y = 0.02
                break
            case 'eraser':
                var eraserMaterial = new THREE.MeshLambertMaterial({color: '#ff0000', side: THREE.DoubleSide}),
                    mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), eraserMaterial)
                mesh.rotation.x = Math.PI / 2
                break
            default:
                console.log('Brak typu (' + type + ')')
                return
        }
        mesh.itemName = type
        Scene.add(mesh)
        Scene.remove(Picker.getDraggedMesh())
        Picker.addDraggedMesh(mesh)
    }
}