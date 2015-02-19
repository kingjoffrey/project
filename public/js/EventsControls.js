EventsControls = function (camera, domElement) {

    var _this = this;

    this.button = null
    this.camera = camera;
    this.container = ( domElement !== undefined ) ? domElement : document;
    this.fixed = new THREE.Vector3(0, 0, 0);

    this.focused = null; // выделенный объект
    this.raycaster = new THREE.Raycaster();

    this._mouse = new THREE.Vector2();

    // API

    this.objects = [];
    this.intersects = [];

    this.previous = new THREE.Vector3(0, 0, 0);

    this.onclick = function () {
    }
    this.mousemove = function () {
    }

    this.attach = function (object) {
        if (object instanceof THREE.Mesh) {
            this.objects.push(object);
        }
    }

    this.detach = function (object) {
        this.objects.splice(this.objects.indexOf(object), 1);
    }

    this.setFocus = function (object) {
        _this.focusedItem = _this.objects.indexOf(object);
        if (object.userData.parent) {
            this.focused = object.userData.parent;
            this.previous.copy(this.focused.position);
        }
        else {
            this.focused = object;
            this.previous.copy(this.focused.position);
        }
    }

    this._setFocusNull = function () {
        this.focused = null;
    }

    this.select = function (object) {
        _this.mouseOveredItem = _this.objects.indexOf(object);
    }

    this._rayGet = function () {
        if (_this.camera instanceof THREE.OrthographicCamera) {
            var vector = new THREE.Vector3(_this._mouse.x, _this._mouse.y, -1).unproject(this.camera);
            var direction = new THREE.Vector3(0, 0, -1).transformDirection(this.camera.matrixWorld);
            _this.raycaster.set(vector, direction);
        }
        else {
            var vector = new THREE.Vector3(_this._mouse.x, _this._mouse.y, 1);
            vector.unproject(_this.camera);
            _this.raycaster.set(_this.camera.position, vector.sub(_this.camera.position).normalize());
        }
    }

    function getMousePos(event) {
        var x = event.offsetX == undefined ? event.layerX : event.offsetX;
        var y = event.offsetY == undefined ? event.layerY : event.offsetY;

        _this._mouse.x = ( x / _this.container.width ) * 2 - 1;
        _this._mouse.y = -( y / _this.container.height ) * 2 + 1;

        var vector = new THREE.Vector3(_this._mouse.x, _this._mouse.y, 0.5);
        return vector;
    }

    function onContainerMouseDown(event) {
        _this.button = event.button

        _this._rayGet();
        _this.intersects = _this.raycaster.intersectObjects(_this.objects, true);

        if (_this.intersects.length > 0) {
            _this.setFocus(_this.intersects[0].object);
            _this.focusedDistance = _this.intersects[0].distance;
            _this.focusedPoint = _this.intersects[0].point;
            _this.onclick();
        }
        else {
            _this._setFocusNull();
        }
    }

    function onContainerMouseMove(event) {
        getMousePos(event)
        _this.mousemove()
    }

    function onContainerMouseUp(event) {
        event.preventDefault();

        if (_this.focused) {
            _this.focused = null;
        }
    }

    this.container.addEventListener('mousedown', onContainerMouseDown, false);	// мышка нажата
    this.container.addEventListener('mousemove', onContainerMouseMove, false);   // получение координат мыши
    this.container.addEventListener('mouseup', onContainerMouseUp, false);       // мышка отпущена

};
