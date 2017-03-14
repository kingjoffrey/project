var EditorGround = new function () {
    var groundMesh,
        createGround = function (x, y, canvas) {
            var maxX = x * 2,
                maxY = y * 2

            var texture = new THREE.Texture(canvas)
            texture.needsUpdate = true

            groundMesh = new THREE.Mesh(new THREE.PlaneBufferGeometry(maxX, maxY), new THREE.MeshLambertMaterial({
                map: texture,
                side: THREE.DoubleSide
            }))

            groundMesh.rotation.x = Math.PI / 2
            groundMesh.position.set(maxX / 2, 0, maxY / 2)

            GameScene.add(groundMesh)

            return groundMesh
        }

    this.init = function (x, y, canvas) {
        createGround(x, y, canvas)
    }
}