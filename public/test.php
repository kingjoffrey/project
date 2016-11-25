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
    <title>three.js webgl - animation - skinning</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <style>
        body {
            color: #fff;
            font-family:Monospace;
            font-size:13px;
            text-align:center;

            background-color: #fff;
            margin: 0px;
            overflow: hidden;
        }

        #info {
            position: absolute;
            top: 0px; width: 100%;
            padding: 5px;
        }
    </style>
</head>
<body>
<div id="container"></div>
<div id="info">
    <a href="http://threejs.org" target="_blank">three.js</a> - Skeletal Animation Blending
    <br><br> Adjust blend weights to affect the animations that are currently playing.
    <br> Cross fades (and warping) blend between 2 animations and end with a single animation.
</div>

<script src="https://threejs.org/examples/../build/three.js"></script>

<script src="https://threejs.org/examples/js/Detector.js"></script>
<script src="https://threejs.org/examples/js/libs/stats.min.js"></script>
<script src="https://threejs.org/examples/js/controls/OrbitControls.js"></script>
<script src="/js/BlendCharacter.js"></script>
<script src="/js/BlendCharacterGui.js"></script>
<script src="https://threejs.org/examples/js/libs/dat.gui.min.js"></script>

<script>

    if ( ! Detector.webgl ) Detector.addGetWebGLMessage();

    var container, stats;

    var blendMesh, helper, camera, scene, renderer, controls;

    var clock = new THREE.Clock();
    var gui = null;

    var isFrameStepping = false;
    var timeToStep = 0;

    init();

    function init() {

        container = document.getElementById( 'container' );

        scene = new THREE.Scene();
        scene.add ( new THREE.AmbientLight( 0xffffff ) );

        renderer = new THREE.WebGLRenderer( { antialias: true, alpha: false } );
        renderer.setClearColor( 0x777777 );
        renderer.setPixelRatio( window.devicePixelRatio );
        renderer.setSize( window.innerWidth, window.innerHeight );
        renderer.autoClear = true;

        container.appendChild( renderer.domElement );

        //

        stats = new Stats();
        container.appendChild( stats.dom );

        //

        window.addEventListener( 'resize', onWindowResize, false );

        // listen for messages from the gui
        window.addEventListener( 'start-animation', onStartAnimation );
        window.addEventListener( 'stop-animation', onStopAnimation );
        window.addEventListener( 'pause-animation', onPauseAnimation );
        window.addEventListener( 'step-animation', onStepAnimation );
        window.addEventListener( 'weight-animation', onWeightAnimation );
        window.addEventListener( 'crossfade', onCrossfade );
        window.addEventListener( 'warp', onWarp );
        window.addEventListener( 'toggle-show-skeleton', onShowSkeleton );
        window.addEventListener( 'toggle-show-model', onShowModel );

        blendMesh = new THREE.BlendCharacter();
//        blendMesh.load( "https://threejs.org/examples/models/skinned/marine/marine_anims_core.json", start );
        blendMesh.load( "/models/PIKMAN-WALK.json", start );

    }

    function onWindowResize() {

        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();

        renderer.setSize( window.innerWidth, window.innerHeight );

    }

    function onStartAnimation( event ) {

        var data = event.detail;

        blendMesh.stopAll();
        blendMesh.unPauseAll();

        // the blend mesh will combine 1 or more animations
        for ( var i = 0; i < data.anims.length; ++i ) {

            blendMesh.play(data.anims[i], data.weights[i]);

        }

        isFrameStepping = false;

    }

    function onStopAnimation( event ) {

        blendMesh.stopAll();
        isFrameStepping = false;

    }

    function onPauseAnimation( event ) {

        ( isFrameStepping ) ? blendMesh.unPauseAll(): blendMesh.pauseAll();

        isFrameStepping = false;

    }

    function onStepAnimation( event ) {

        blendMesh.unPauseAll();
        isFrameStepping = true;
        timeToStep = event.detail.stepSize;
    }

    function onWeightAnimation(event) {

        var data = event.detail;
        for ( var i = 0; i < data.anims.length; ++i ) {

            blendMesh.applyWeight( data.anims[ i ], data.weights[ i ] );

        }

    }

    function onCrossfade(event) {

        var data = event.detail;

        blendMesh.stopAll();
        blendMesh.crossfade( data.from, data.to, data.time );

        isFrameStepping = false;

    }

    function onWarp( event ) {

        var data = event.detail;

        blendMesh.stopAll();
        blendMesh.warp( data.from, data.to, data.time );

        isFrameStepping = false;

    }

    function onShowSkeleton( event ) {

        var shouldShow = event.detail.shouldShow;
        helper.visible = shouldShow;

    }

    function onShowModel( event ) {

        var shouldShow = event.detail.shouldShow;
        blendMesh.showModel( shouldShow );

    }

    function start() {

        blendMesh.rotation.y = Math.PI * -135 / 180;
        scene.add( blendMesh );

        var aspect = window.innerWidth / window.innerHeight;
        var radius = blendMesh.geometry.boundingSphere.radius;

        camera = new THREE.PerspectiveCamera( 45, aspect, 1, 10000 );
        camera.position.set( 0.0, radius, radius * 3.5 );

        controls = new THREE.OrbitControls( camera );
        controls.target.set( 0, radius, 0 );
        controls.update();

        // Set default weights
//        blendMesh.applyWeight( 'idle', 1 / 3 );
        blendMesh.applyWeight( 'walk', 1 / 3 );
//        blendMesh.applyWeight( 'run', 1 / 3 );

        gui = new BlendCharacterGui(blendMesh);

        // Create the debug visualization

        helper = new THREE.SkeletonHelper( blendMesh );
        helper.material.linewidth = 3;
        scene.add( helper );

        helper.visible = false;

        animate();
    }

    function animate() {

        requestAnimationFrame( animate, renderer.domElement );

        stats.begin();

        // step forward in time based on whether we're stepping and scale

        var scale = gui.getTimeScale();
        var delta = clock.getDelta();
        var stepSize = (!isFrameStepping) ? delta * scale: timeToStep;

        // modify blend weights

        blendMesh.update( stepSize );
        helper.update();
        gui.update( blendMesh.mixer.time );

        renderer.render( scene, camera );
        stats.end();

        // if we are stepping, consume time
        // ( will equal step size next time a single step is desired )

        timeToStep = 0;

    }

</script>

</body>
</html>
