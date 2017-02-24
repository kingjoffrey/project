var EditorModels = new function () {
    this.createMesh = function (type) {
        switch (type) {
            case 'castle':
                var mesh = new THREE.Mesh(Models.getCastleModels()[0].geometry, new THREE.MeshLambertMaterial({
                    color: '#3B3028',
                    side: THREE.DoubleSide
                }))
                mesh.scale.x = 0.2
                mesh.scale.y = 0.2
                mesh.scale.z = 0.2
                break
            case 'ruin':
                var mesh = new THREE.Mesh(Models.getRuinModel().geometry, new THREE.MeshPhongMaterial({
                    color: '#FFD700',
                    side: THREE.DoubleSide
                }))
                mesh.scale.x = 0.05
                mesh.scale.y = 0.05
                mesh.scale.z = 0.05
                break
            case 'tower':
                var mesh = new THREE.Mesh(Models.getTowerModel().geometry, new THREE.MeshLambertMaterial({
                    color: '#6B6B6B',
                    side: THREE.DoubleSide
                }))
                mesh.scale.x = 0.3
                mesh.scale.y = 0.3
                mesh.scale.z = 0.3
                break
            case 'road':
                var mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.MeshLambertMaterial({
                    color: '#000000',
                    side: THREE.DoubleSide
                }))
                mesh.rotation.x = Math.PI / 2
                mesh.position.y = 0.02
                break
            case 'forest':
                var mesh = new THREE.Mesh(Models.getTreeModel().geometry, Models.getTreeModel().material)
                mesh.scale.x = 0.3
                mesh.scale.y = 0.3
                mesh.scale.z = 0.3
                break
            case 'swamp':
                var mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), new THREE.MeshLambertMaterial({
                    color: '#4f4224',
                    side: THREE.DoubleSide
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
        GameScene.add(mesh)
        GameScene.remove(PickerEditor.getDraggedMesh())
        PickerEditor.addDraggedMesh(mesh)
    }
}