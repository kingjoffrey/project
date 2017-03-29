var Animation = new function () {
    var rotWorldMatrix,
        rotateAroundWorldAxis = function (object, axis, radians) {
            rotWorldMatrix = new THREE.Matrix4();
            rotWorldMatrix.makeRotationAxis(axis.normalize(), radians);

            // old code for Three.JS pre r54:
            //  rotWorldMatrix.multiply(object.matrix);
            // new code for Three.JS r55+:
            rotWorldMatrix.multiply(object.matrix);                // pre-multiply

            object.matrix = rotWorldMatrix;

            // old code for Three.js pre r49:
            // object.rotation.getRotationFromMatrix(object.matrix, object.scale);
            // old code for Three.js pre r59:
            // object.rotation.setEulerFromRotationMatrix(object.matrix);
            // code for r59+:
            object.rotation.setFromRotationMatrix(object.matrix);
        }

    this.rotate = function (mesh) {
        var xAxis = new THREE.Vector3(1, 0, 0)
        rotateAroundWorldAxis(mesh, xAxis, Math.PI / 180)
    }
}
