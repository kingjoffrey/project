<?php
/////**
//// * ver. 0001
//// */
////date_default_timezone_set('Europe/Warsaw');
////
////class Aaa
////{
////    public $aaa = array();
////    public $a = 20;
////
////    function a()
////    {
////        for ($i = 0; $i < 10; $i++) {
////            $this->aaa[$i] = new Bbb($i);
////        }
////    }
////
////    function getFirstBbb()
////    {
////        return $this->aaa[0];
////    }
////
////    function get()
////    {
////        return $this->a;
////    }
////
////    function loop()
////    {
////        $value = 10;
////        echo date('H:i;s', time()) . "\n";
////
////        for ($i = 0; $i < 1000000000; $i++) {
////            if ($this->a >= $value) {
////
////            }
////        }
////
////        echo date('H:i;s', time()) . "\n";
////    }
////}
////
////class Bbb
////{
////    private $i;
////
////    public function __construct($i)
////    {
////        $this->i = $i;
////    }
////
////    function b()
////    {
////        echo 'b' . $this->i . "\n";
////    }
////
////    function setI($i)
////    {
////        $this->i = $i;
////    }
////}
////
////$Aaa = new Aaa();
////$Aaa->a();
////
////$Bbb = $Aaa->getFirstBbb();
////
////$Bbb->b();
////$Bbb->setI(1);
////$Bbb->b();
////$Aaa->getFirstBbb()->b();
////$Aaa->getFirstBbb()->setI(2);
////$Bbb->b();
//
////echo phpinfo();
//
//class STD extends Thread{
//    public function put(){
//
//        $this->synchronized(function(){
//            for($i=0;$i<7;$i++){
//
//    printf("%d\n",$i);
//    $this->notify();
//    if($i < 6)
//    $this->wait();
//    else
//        exit();
//    sleep(1);
//}
//        });
//
//    }
//
//        public function flush(){
//
//$this->synchronized(function(){
//            for($i=0;$i<7;$i++){
//    flush();
//    $this->notify();
//    if($i < 6)
//    $this->wait();
//    else
//        exit();
//    }
//});
//}
//}
//
//class A extends Thread{
//    private $std;
//    public function __construct($std){
//        $this->std = $std;
//    }
//    public function run(){
//        $this->std->put();
//    }
//}
//
//class B extends Thread{
//    private $std;
//    public function __construct($std){
//        $this->std = $std;
//    }
//    public function run(){
//        $this->std->flush();
//    }
//}
//ob_end_clean();
//echo str_repeat(" ", 1024);
//$std = new STD();
//$ta = new A($std);
//$tb = new B($std);
//$ta->start();
//$tb->start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>three.js webgl - cameras</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <style>
        body {
            color: #808080;
            font-family: Monospace;
            font-size: 13px;
            text-align: center;

            background-color: #000;
            margin: 0px;
            overflow: hidden;
        }

        #info {
            position: absolute;
            top: 0px;
            width: 100%;
            padding: 5px;
            z-index: 100;
        }

        a {

            color: #0080ff;
        }

        b {
            color: lightgreen
        }
    </style>
</head>
<body>
<div id="info"><a href="http://threejs.org" target="_blank">three.js</a> - cameras<br/>
    <b>O</b> orthographic <b>P</b> perspective
</div>

<script src="https://threejs.org/examples/../build/three.js"></script>
<script src="https://threejs.org/examples/js/libs/stats.min.js"></script>
<script src="/models/hero.json"></script>

<script>

    var SCREEN_WIDTH = window.innerWidth;
    var SCREEN_HEIGHT = window.innerHeight;
    var aspect = SCREEN_WIDTH / SCREEN_HEIGHT;

    var container, stats;
    var camera, scene1, scene2, renderer, mesh, mesh2;
    var cameraRig, activeCamera, activeHelper;
    var cameraPerspective, cameraOrtho;
    var cameraPerspectiveHelper, cameraOrthoHelper;
    var frustumSize = 600;

    var loader = new THREE.JSONLoader()
    var tl = new THREE.TextureLoader()

    init();

    function init() {

        container = document.createElement('div');
        document.body.appendChild(container);

        scene1 = new THREE.Scene();
        scene2 = new THREE.Scene();

        //

        camera = new THREE.PerspectiveCamera(50, 0.5 * aspect, 1, 10000);
        camera.position.z = 2500;

        cameraPerspective = new THREE.PerspectiveCamera(50, 0.5 * aspect, 150, 1000);

        cameraPerspectiveHelper = new THREE.CameraHelper(cameraPerspective);
        scene1.add(cameraPerspectiveHelper);

        //
        cameraOrtho = new THREE.OrthographicCamera(0.5 * frustumSize * aspect / -2, 0.5 * frustumSize * aspect / 2, frustumSize / 2, frustumSize / -2, 150, 1000);

        cameraOrthoHelper = new THREE.CameraHelper(cameraOrtho);
        scene1.add(cameraOrthoHelper);

        //

        activeCamera = cameraPerspective;
        activeHelper = cameraPerspectiveHelper;


        // counteract different front orientation of cameras vs rig

        cameraOrtho.rotation.y = Math.PI;
        cameraPerspective.rotation.y = Math.PI;

        cameraRig = new THREE.Group();

        cameraRig.add(cameraPerspective);
        cameraRig.add(cameraOrtho);

        scene1.add(cameraRig);

        //

        hero.scale = 0.009
        boguszModel = loader.parse(hero)

        tl.load(window.location.origin + '/img/modelMaps/hero.png', function (texture) {
            mesh = new THREE.Mesh(
                boguszModel.geometry,
                new THREE.MeshBasicMaterial({
                    map: texture,
                    color: 0xffffff,
                    wireframe: true,
                    side: THREE.DoubleSide
                })
            );

            console.log('aaa')

            scene1.add(mesh);

        mesh2 = new THREE.Mesh(
					new THREE.SphereBufferGeometry( 100, 16, 8 ),
					new THREE.MeshBasicMaterial( { color: 0xffffff, wireframe: true } )
				);
				scene2.add( mesh2 );


            renderer = new THREE.WebGLRenderer({antialias: true});
            renderer.setPixelRatio(window.devicePixelRatio);
            renderer.setSize(SCREEN_WIDTH, SCREEN_HEIGHT);
            renderer.domElement.style.position = "relative";
            container.appendChild(renderer.domElement);

            renderer.autoClear = false;

            //

            stats = new Stats();
            container.appendChild(stats.dom);

            //

            window.addEventListener('resize', onWindowResize, false);
            document.addEventListener('keydown', onKeyDown, false);

            animate();

        })
    }

    //

    function onKeyDown(event) {

        switch (event.keyCode) {

            case 79: /*O*/

                activeCamera = cameraOrtho;
                activeHelper = cameraOrthoHelper;

                break;

            case 80: /*P*/

                activeCamera = cameraPerspective;
                activeHelper = cameraPerspectiveHelper;

                break;

        }

    }

    //

    function onWindowResize(event) {

        SCREEN_WIDTH = window.innerWidth;
        SCREEN_HEIGHT = window.innerHeight;
        aspect = SCREEN_WIDTH / SCREEN_HEIGHT;

        renderer.setSize(SCREEN_WIDTH, SCREEN_HEIGHT);

        camera.aspect = 0.5 * aspect;
        camera.updateProjectionMatrix();

        cameraPerspective.aspect = 0.5 * aspect;
        cameraPerspective.updateProjectionMatrix();

        cameraOrtho.left = -0.5 * frustumSize * aspect / 2;
        cameraOrtho.right = 0.5 * frustumSize * aspect / 2;
        cameraOrtho.top = frustumSize / 2;
        cameraOrtho.bottom = -frustumSize / 2;
        cameraOrtho.updateProjectionMatrix();

    }

    //

    function animate() {

        requestAnimationFrame(animate);

        render();
        stats.update();

    }


    function render() {

        var r = Date.now() * 0.0005;

        mesh.position.x = 700 * Math.cos(r);
        mesh.position.z = 700 * Math.sin(r);
        mesh.position.y = 700 * Math.sin(r);

        if (activeCamera === cameraPerspective) {

            cameraPerspective.fov = 35 + 30 * Math.sin(0.5 * r);
            cameraPerspective.far = mesh.position.length();
            cameraPerspective.updateProjectionMatrix();

            cameraPerspectiveHelper.update();
            cameraPerspectiveHelper.visible = true;

            cameraOrthoHelper.visible = false;

        } else {

            cameraOrtho.far = mesh.position.length();
            cameraOrtho.updateProjectionMatrix();

            cameraOrthoHelper.update();
            cameraOrthoHelper.visible = true;

            cameraPerspectiveHelper.visible = false;

        }

        cameraRig.lookAt(mesh.position);

        renderer.clear();

        activeHelper.visible = false;

        renderer.setViewport(0, 0, 100, 100);
        renderer.render(scene1, activeCamera);

        activeHelper.visible = true;

        renderer.setViewport(SCREEN_WIDTH / 2, 0, SCREEN_WIDTH / 2, SCREEN_HEIGHT);
        renderer.render(scene2, camera);


//        renderer.clear();
//        renderer.render(scene, camera);
//        renderer.clearDepth();
//        renderer.render(scene2, camera);

    }


</script>

</body>
</html>