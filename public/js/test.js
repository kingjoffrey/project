var Test = new function () {
    var scene = new THREE.Scene(),
        camera = new THREE.PerspectiveCamera(10, window.innerWidth / window.innerHeight, 1, 1000),
        renderer = new THREE.WebGLRenderer()

    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.shadowMap.enabled = true

    camera.position.set(-5, 5, 5);
    camera.rotation.order = 'YXZ';
    camera.rotation.y = -Math.PI / 4;
    camera.rotation.x = Math.atan(-1 / Math.sqrt(2));
    scene.add(camera)

    scene.add(new THREE.AmbientLight(0x222222));

    var light = new THREE.PointLight(0xffffff, 0.7);
    camera.add(light);

    this.init = function () {
        $('body').append(renderer.domElement);
        Test.render();
    }
    this.render = function () {
        requestAnimationFrame(Test.render);
        renderer.render(scene, camera);
    }
    this.getScene = function getScene() {
        return scene
    }
}

var CreateSimpleMesh = new function () {
    var grassGeometry = new THREE.BufferGeometry(),
        grassVertexPositions = [],
        imageArray = [
            [0, 0],
            [1, 0],
            [1, 1],
            [0, 1]
        ],
        image = new Image()

    this.init = function () {
        image.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wwLAi03pl5F1AAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAEkklEQVR42u3dTWzTdRzH8XfXdW03t3XPbmyMlLkNZA9kcwuKBBVBEjXEGDExEAwH44GDJpp4QYnCQcIZoiZGUaKeCFHjIBB5UnnSKW5zj7AVWvfvxsbWre22Dg+1SmHA/iwbHD7v2+/fZYfvK/+n9vCzrDmx7hrqvilBIxCIuk2JGsHsNth0hcvf9dB/1iBkBLFnOkgrc+HeWEr6oow7gwy1DuJt8NB/zs+oJ4DFmoAjx4GrMoviF92klqRryiY69dqxuHXICBIyghgnfFS/X0feyoLbg/y8+ccbjkwy0hNgpCfA5e+7qdxaS/6qQk16mrmWZFL4fDGZNTnYM+wEuodp2fUHg39eof2j5juDpJW7mLd2PlmP5ODMT2FyPMJIT4AL+9rpPeKlbXeTQExUv2dF/HwfclGxtYbjLx0i+PfonS9Zyz5ZGX/XtyWQXp7B4jer6D3iZWJkQlOe6Y3bGR178rwU8zf1ieAEwcsjXNjXDkD+ap0dM+3iVx0APDjFleaWIA3L98etnQXJlL6+mOL1JZroDPIe9HDhy3aSC1NY8HLJ3b+HjA2ECfeHQe/1M8I4/8E5rA4r1dvrsNqt0z9D1pxYF4UYGmPor0HadjfR/U0nWKB8S4WmazLPgYs072wEC1S+W0vqwvS7e1NPSksiuy6XpTvqAPAduqTpmr1nfN1B84eNcA0efnspuY/nz/xNfXI8eq2KjOopy0ydn7XS8XELAIvfqqbw2WJzX52cfeMk819wk74kE1uqjYnhcQabB2jf0xR90anM0pRNFMMAaN7ZGL1sXdcT364lyWW/NUj/GT/9Z/xT/nNbmo3yLUs05VnMcuPvIQPn+7l0oJsrv/oJ94VIsCXgLEghuz6XBetLsGc7NLXZfGm88UBGRRYZFbos3av0e4hAlEAEogQiECUQgSiBCEQJRAlEIEogAlECEYgSiECUQJRABKIEIhAlEIEogQhECUQJRCBKIAJRAhGIEohAlECUQASiBCIQJRCBKIEIRAlECUQgSiACUQIRiBKIQJRAlEAEogQiECUQgSiBCETdu6bcFGxyLELX3ja8P3gI+YM4cpwUPFOEe2MZCTYZmi3QPYxxzIdx3MfV5gHg/20JpwXy+7azGEd9/62DvlE6P20l0DVM9fY6TdhkJ185fPeXLP8vvRhHfViTE6nZtYxVh5+jZtcyrMmJ9B710nfa0IRNlrIglYWvlvHY50+aB/Ed9ADg3lBKdn0eVruV7Po83BtKAfA2eDRhky3/4ilKNi/iAXeaeZCrLYMA5DyaF3c8th5qGdCE5/IpK9wXBMBZEL/Xd2wd8oc0tbkEiYQiADftZBxbR8LaenVOQayO2OAj8VDhGFSipjaXIPZsZ/RR1zsSdzy2duRop885BUlf5Io+/v7UG/84/O86rdylqc0lSP7TRQB07W2j77TB5FiEvtMGXXvbop+vLtLUZrGbNicG+O2dUxjHfTf9ce6KfJbuqNfUTNawfP9tP7/+a5Qpv5iq2laLe1MZjjwnFqsFR54T96Yyqt6r1XTvxRmi7qN7iBKIEohA1DT7B5cwUaoY0hehAAAAAElFTkSuQmCC'
        var texture = new THREE.Texture();
        texture.image = image;
        image.onload = function () {
            texture.needsUpdate = true;
        };
        // first triangle
        grassVertexPositions.push([0, 0, 0])
        grassVertexPositions.push([1, 0, 0])
        grassVertexPositions.push([0, 1, 0])

        // second triangle
        grassVertexPositions.push([1, 1, 0])
        grassVertexPositions.push([0, 1, 0])
        grassVertexPositions.push([1, 0, 0])

        var grassVertices = new Float32Array(grassVertexPositions.length * 3),
            normals = new Float32Array(grassVertexPositions.length * 3),
            colors = new Float32Array(grassVertexPositions.length * 3),
            uvs = new Float32Array(grassVertexPositions.length * 2)

        for (var i = 0; i < grassVertexPositions.length; i++) {
            var index = 3 * i
            grassVertices[index + 0] = grassVertexPositions[i][0]
            grassVertices[index + 1] = grassVertexPositions[i][1]
            grassVertices[index + 2] = grassVertexPositions[i][2]
        }

        uvs[0] = imageArray[0][0]
        uvs[1] = imageArray[0][1]

        uvs[2] = imageArray[1][0]
        uvs[3] = imageArray[1][1]

        uvs[4] = imageArray[3][0]
        uvs[5] = imageArray[3][1]

        uvs[6] = imageArray[2][0]
        uvs[7] = imageArray[2][1]

        uvs[8] = imageArray[3][0]
        uvs[9] = imageArray[3][1]

        uvs[10] = imageArray[1][0]
        uvs[11] = imageArray[1][1]

        grassGeometry.addAttribute('position', new THREE.BufferAttribute(grassVertices, 3))
        grassGeometry.addAttribute('normal', new THREE.BufferAttribute(normals, 3))
        grassGeometry.addAttribute('color', new THREE.BufferAttribute(colors, 3))
        grassGeometry.addAttribute('uv', new THREE.BufferAttribute(uvs, 2))

        grassGeometry.computeVertexNormals()

        //var textureLoader = new THREE.TextureLoader();
        //textureLoader.load('/img/testface.png', function (texture) {

            var grassMaterial = new THREE.MeshLambertMaterial({map: texture}),
                grassMesh = new THREE.Mesh(grassGeometry, grassMaterial)

            grassMesh.rotation.x = -Math.PI / 2;
            Test.getScene().add(grassMesh)

            var helper = new THREE.WireframeHelper(grassMesh, 0xff00ff); // alternate
            helper.material.linewidth = 1;
            Test.getScene().add(helper);

            console.log(grassMesh.geometry.attributes)
        //});
    }
}

$(document).ready(function () {
    Test.init()
    CreateSimpleMesh.init()
});
