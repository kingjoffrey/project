var EditorModels = new function () {
    this.createMesh = function (type) {
        switch (type) {
            case 'castle':
                var mesh = new THREE.Mesh(Models.getCastleModels()[0].geometry, new THREE.MeshLambertMaterial({
                    color: '#3B3028',
                    side: THREE.DoubleSide
                }))
                break
            case 'ruin':
                var mesh = new THREE.Mesh(Models.getRuinModel().geometry, new THREE.MeshPhongMaterial({
                    color: '#FFD700',
                    side: THREE.DoubleSide
                }))
                break
            case 'tower':
                var mesh = new THREE.Mesh(Models.getTowerModel().geometry, new THREE.MeshLambertMaterial({
                    color: '#6B6B6B',
                    side: THREE.DoubleSide
                }))
                break
            case 'road':
                var mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.MeshLambertMaterial({
                    map: Models.getRoadTexture().p,
                    side: THREE.DoubleSide,
                    transparent: true,
                    opacity: 0.5
                }))
                mesh.rotation.x = Math.PI / 2
                mesh.position.y = 0.02
                break
            case 'forest':
                var mesh = new THREE.Mesh(Models.getTreeModel().geometry, Models.getTreeModel().material)
                break
            case 'swamp':
                var mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.MeshLambertMaterial({
                    map: Models.getSwampTexture(),
                    side: THREE.DoubleSide,
                    transparent: true,
                    opacity: 0.5
                }))
                mesh.rotation.x = Math.PI / 2
                mesh.position.y = 0.02
                break
            case 'eraser':
                var mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.MeshLambertMaterial({
                    color: '#ff0000',
                    side: THREE.DoubleSide
                }))
                mesh.rotation.x = Math.PI / 2
                mesh.position.y = 0.02
                break
            case 'up':
                var mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.MeshLambertMaterial({
                    color: '#00ff00',
                    side: THREE.DoubleSide
                }))
                mesh.rotation.x = Math.PI / 2
                mesh.position.y = 0.02
                break
            case 'down':
                var mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.MeshLambertMaterial({
                    color: '#0000ff',
                    side: THREE.DoubleSide
                }))
                mesh.rotation.x = Math.PI / 2
                mesh.position.y = 0.02
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