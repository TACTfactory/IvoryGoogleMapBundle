<?php

namespace Ivory\GoogleMapBundle\Tests\Templating\Helper;

use Ivory\GoogleMapBundle\Templating\Helper;
use Ivory\GoogleMapBundle\Templating\Helper\Base as BaseHelper;
use Ivory\GoogleMapBundle\Templating\Helper\Controls as ControlsHelper;
use Ivory\GoogleMapBundle\Templating\Helper\Overlays as OverlaysHelper;

use Ivory\GoogleMapBundle\Model;
use Ivory\GoogleMapBundle\Model\Base;
use Ivory\GoogleMapBundle\Model\Controls;
use Ivory\GoogleMapBundle\Model\Overlays;

/**
 * Map helper test
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class MapHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Ivory\GoogleMapBundle\Templating\Helper\MapHelper
     */
    protected static $mapHelper = null;
    
    /**
     * @override
     */
    protected function setUp()
    {
        self::$mapHelper = new Helper\MapHelper(
            new BaseHelper\CoordinateHelper(),
            new Helper\MapTypeIdHelper(),
            new ControlsHelper\MapTypeControlHelper(
                new Helper\MapTypeIdHelper(),
                new ControlsHelper\ControlPositionHelper(),
                new ControlsHelper\MapTypeControlStyleHelper()
            ),
            new OverlaysHelper\MarkerHelper(
                new BaseHelper\CoordinateHelper(),
                new OverlaysHelper\InfoWindowHelper(new BaseHelper\CoordinateHelper()),
                new OverlaysHelper\MarkerImageHelper(new BaseHelper\PointHelper(), new BaseHelper\SizeHelper()),
                new OverlaysHelper\MarkerShapeHelper()
            ),
            new BaseHelper\BoundHelper(new BaseHelper\CoordinateHelper()),
            new OverlaysHelper\InfoWindowHelper(new BaseHelper\CoordinateHelper()),
            new OverlaysHelper\PolylineHelper(new BaseHelper\CoordinateHelper()),
            new OverlaysHelper\PolygonHelper(new BaseHelper\CoordinateHelper()),
            new OverlaysHelper\RectangleHelper(new BaseHelper\BoundHelper(new BaseHelper\CoordinateHelper())),
            new OverlaysHelper\CircleHelper(new BaseHelper\CoordinateHelper()),
            new OverlaysHelper\GroundOverlayHelper(new BaseHelper\BoundHelper(new BaseHelper\CoordinateHelper())),
            new Helper\EventHelper()
        );
    }
    
    /**
     * Checks the render container method
     */
    public function testRenderContainer()
    {
        $mapTest = new Model\Map();
        $mapTest->setHtmlContainerId('html_container_id');
        
        $this->assertEquals(self::$mapHelper->renderContainer($mapTest), '<div id="html_container_id"></div>'.PHP_EOL);
    }
    
    /**
     * Checks the render stylesheets method
     */
    public function testRenderStylesheets()
    {
        $mapTest = new Model\Map();
        $mapTest->setHtmlContainerId('html_container_id');
        
        $mapTest->setStylesheetOptions(array(
            'height' => '100px',
            'width' => '200px',
            'option1' => 'value1'
        ));
        
        $this->assertEquals(self::$mapHelper->renderStylesheets($mapTest),
            '<style type="text/css">'.PHP_EOL.
            '#html_container_id{'.PHP_EOL.
            'width:200px;'.PHP_EOL.
            'height:100px;'.PHP_EOL.
            'option1:value1;'.PHP_EOL.
            '}'.PHP_EOL.
            '</style>'.PHP_EOL
        );
    }
    
    /**
     * Checks the render javascripts method
     */
    public function testRenderJavascripts()
    {
        $mapTest = new Model\Map();
        $mapTest->setHtmlContainerId('html_container_id');
        
        $centerTest = new Base\Coordinate(1.1, 2.1, true);
        $mapTest->setCenter($centerTest);
        
        $mapTest->setMapOption('mapTypeId', 'satellite');
        $mapTest->setMapOption('zoom', 5);
        
        $this->assertEquals(self::$mapHelper->renderJavascripts($mapTest),
            '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>'.PHP_EOL.
            '<script type="text/javascript">'.PHP_EOL.
            'var '.$mapTest->getJavascriptVariable().' = new google.maps.Map(document.getElementById("html_container_id"), {"mapTypeId":google.maps.MapTypeId.SATELLITE,"zoom":5});'.PHP_EOL.
            $mapTest->getJavascriptVariable().'.setCenter(new google.maps.LatLng(1.1, 2.1, true));'.PHP_EOL.
            '</script>'.PHP_EOL
        );
    }
    
    /**
     * Checks the render map method
     */
    public function testRenderMap()
    {
        $mapTest = new Model\Map();
        $mapTest->setHtmlContainerId('html_container_id');
        
        $mapTest->setMapOption('mapTypeId', 'satellite');
        $mapTest->setMapOption('zoom', 5);
        $mapTest->setMapTypeControl(array(Model\MapTypeId::ROADMAP), Controls\ControlPosition::BOTTOM_CENTER, Controls\MapTypeControlStyle::DROPDOWN_MENU);
        
        $this->assertEquals(self::$mapHelper->renderMap($mapTest),
            'var '.$mapTest->getJavascriptVariable().' = new google.maps.Map(document.getElementById("html_container_id"), {"mapTypeId":google.maps.MapTypeId.SATELLITE,"mapTypeControlOptions":{"mapTypeIds":[google.maps.MapTypeId.ROADMAP],"position":google.maps.ControlPosition.BOTTOM_CENTER,"style":google.maps.MapTypeControlStyle.DROPDOWN_MENU},"zoom":5});'.PHP_EOL
        );
        
        $mapTest->setMapTypeControl(null);
        $mapTest->setAutoZoom(true);
        
        $this->assertEquals(self::$mapHelper->renderMap($mapTest),
            'var '.$mapTest->getJavascriptVariable().' = new google.maps.Map(document.getElementById("html_container_id"), {"mapTypeId":google.maps.MapTypeId.SATELLITE});'.PHP_EOL
        );
    }
    
    /**
     * Checks the render center method
     */
    public function testRenderCenter()
    {
        $mapTest = new Model\Map();
        $mapTest->setCenter(new Base\Coordinate(1.1, 2.1, true));
        
        $this->assertEquals(self::$mapHelper->renderCenter($mapTest),
            $mapTest->getJavascriptVariable().'.setCenter(new google.maps.LatLng(1.1, 2.1, true));'.PHP_EOL
        );
    }
    
    /**
     * Checks the render bound method
     */
    public function testRenderBound()
    {
        $mapTest = new Model\Map();
        $boundTest = new Base\Bound();
        $boundTest->setSouthWest(-1.1, 2.1, true);
        $boundTest->setNorthEast(1.1, 2.1, true);
        $mapTest->setBound($boundTest);
        
        $this->assertEquals(self::$mapHelper->renderBound($mapTest),
            'var '.$mapTest->getBound()->getJavascriptVariable().' = new google.maps.LatLngBounds(new google.maps.LatLng(-1.1, 2.1, true), new google.maps.LatLng(1.1, 2.1, true));'.PHP_EOL.
            $mapTest->getJavascriptVariable().'.fitBounds('.$mapTest->getBound()->getJavascriptVariable().');'.PHP_EOL
        );
    }
    
    /**
     * Checks the render markers method
     */
    public function testRenderMarkers()
    {
        $mapTest = new Model\Map();
        $markerTest = new Overlays\Marker();
        $markerTest->setPosition(new Base\Coordinate(1.1, 2.1, false));
        $mapTest->addMarker($markerTest);
        
        $this->assertEquals(self::$mapHelper->renderMarkers($mapTest),
            'var '.$markerTest->getJavascriptVariable().' = new google.maps.Marker({"map":'.$mapTest->getJavascriptVariable().',"position":new google.maps.LatLng(1.1, 2.1, false)});'.PHP_EOL
        );
    }
    
    /**
     * Checks the render info windows method
     */
    public function testRenderInfoWindows()
    {
        $mapTest = new Model\Map();
        $infoWindow = new Overlays\InfoWindow();
        $infoWindow->setPosition(new Base\Coordinate(1.1, 2.1, true));
        $infoWindow->setContent('content');
        $infoWindow->setOpen(true);
        $mapTest->addInfoWindow($infoWindow);
        
        $this->assertEquals(self::$mapHelper->renderInfoWindows($mapTest),
            'var '.$infoWindow->getJavascriptVariable().' = new google.maps.InfoWindow({"position":new google.maps.LatLng(1.1, 2.1, true),"content":"content"});'.PHP_EOL.
            $infoWindow->getJavascriptVariable().'.open('.$mapTest->getJavascriptVariable().');'.PHP_EOL
        );
    }
    
    /**
     * Checks the render polylines method
     */
    public function testRenderPolylines()
    {
        $mapTest = new Model\Map();
        $polylineTest = new Overlays\Polyline();
        $polylineTest->setCoordinates(array(
            new Base\Coordinate(1.1, 2.1, true),
            new Base\Coordinate(3.1, 4.1, true)
        ));
        $mapTest->addPolyline($polylineTest);
        
        $this->assertEquals(self::$mapHelper->renderPolylines($mapTest), 
            'var '.$polylineTest->getJavascriptVariable().' = new google.maps.Polyline({"map":'.$mapTest->getJavascriptVariable().',"path":[new google.maps.LatLng(1.1, 2.1, true),new google.maps.LatLng(3.1, 4.1, true)]});'.PHP_EOL
        );
    }
    
    /**
     * Checks the render polygons method
     */
    public function testRenderPolygons()
    {
        $mapTest = new Model\Map();
        $polygonTest = new Overlays\Polygon();
        $polygonTest->setCoordinates(array(
            new Base\Coordinate(1.1, 2.1, true),
            new Base\Coordinate(3.1, 4.1, true)
        ));
        $mapTest->addPolygon($polygonTest);
        
        $this->assertEquals(self::$mapHelper->renderPolygons($mapTest), 
            'var '.$polygonTest->getJavascriptVariable().' = new google.maps.Polygon({"map":'.$mapTest->getJavascriptVariable().',"paths":[new google.maps.LatLng(1.1, 2.1, true),new google.maps.LatLng(3.1, 4.1, true)]});'.PHP_EOL
        );
    }
    
    /**
     * Checks the render rectangles method
     */
    public function testRenderRectangles()
    {
        $mapTest = new Model\Map();
        $rectangleTest = new Overlays\Rectangle();
        $rectangleTest->getBound()->setSouthWest(new Base\Coordinate(-1.1, -2.1, true));
        $rectangleTest->getBound()->setNorthEast(new Base\Coordinate(1.1, 2.1, true));
        $mapTest->addRectangle($rectangleTest);
        
        $this->assertEquals(self::$mapHelper->renderRectangles($mapTest), 
            'var '.$rectangleTest->getBound()->getJavascriptVariable().' = new google.maps.LatLngBounds(new google.maps.LatLng(-1.1, -2.1, true), new google.maps.LatLng(1.1, 2.1, true));'.PHP_EOL.
            'var '.$rectangleTest->getJavascriptVariable().' = new google.maps.Rectangle({"map":'.$mapTest->getJavascriptVariable().',"bounds":'.$rectangleTest->getBound()->getJavascriptVariable().'});'.PHP_EOL
        );
    }
    
    /**
     * Checks the render circles method
     */
    public function testRenderCircles()
    {
        $mapTest = new Model\Map();
        $circleTest = new Overlays\Circle();
        $circleTest->setCenter(new Base\Coordinate(1.1, 2.1, true));
        $circleTest->setRadius(2);
        $mapTest->addCircle($circleTest);
        
        $this->assertEquals(self::$mapHelper->renderCircles($mapTest), 
            'var '.$circleTest->getJavascriptVariable().' = new google.maps.Circle({"map":'.$mapTest->getJavascriptVariable().',"center":new google.maps.LatLng(1.1, 2.1, true),"radius":2});'.PHP_EOL
        );
    }
    
    /**
     * Checks the render ground overlays method
     */
    public function testRenderGroundOverlays()
    {
        $mapTest = new Model\Map();
        $groundOverlayTest = new Overlays\GroundOverlay();
        $groundOverlayTest->setUrl('url');
        $groundOverlayTest->setBound(new Base\Coordinate(-1.1, -2.1, true), new Base\Coordinate(1.1, 2.1, true));
        $mapTest->addGroundOverlay($groundOverlayTest);
        
        $this->assertEquals(self::$mapHelper->renderGroundOverlays($mapTest), 
            'var '.$groundOverlayTest->getBound()->getJavascriptVariable().' = new google.maps.LatLngBounds(new google.maps.LatLng(-1.1, -2.1, true), new google.maps.LatLng(1.1, 2.1, true));'.PHP_EOL.
            'var '.$groundOverlayTest->getJavascriptVariable().' = new google.maps.GroundOverlay("url", '.$groundOverlayTest->getBound()->getJavascriptVariable().', {"map":'.$mapTest->getJavascriptVariable().'});'.PHP_EOL
        );
    }
   
    /**
     * Checks the render events method
     */
    public function testRenderEvents()
    {
        $mapTest = new Model\Map();
        
        $domEvent = new Model\Event();
        $domEvent->setInstance('instance');
        $domEvent->setEventName('event_name');
        $domEvent->setHandle('handle');
        $domEvent->setCapture(true);
        $mapTest->getEventManager()->addDomEvent($domEvent);
        
        $domEventOnce = new Model\Event();
        $domEventOnce->setInstance('instance');
        $domEventOnce->setEventName('event_name');
        $domEventOnce->setHandle('handle');
        $domEventOnce->setCapture(true);
        $mapTest->getEventManager()->addDomEventOnce($domEventOnce);
        
        $event = new Model\Event();
        $event->setInstance('instance');
        $event->setEventName('event_name');
        $event->setHandle('handle');
        $mapTest->getEventManager()->addEvent($event);
        
        $eventOnce = new Model\Event();
        $eventOnce->setInstance('instance');
        $eventOnce->setEventName('event_name');
        $eventOnce->setHandle('handle');
        $mapTest->getEventManager()->addEventOnce($eventOnce);
        
        $this->assertEquals(self::$mapHelper->renderEvents($mapTest),
            'var '.$domEvent->getJavascriptVariable().' = google.maps.event.addDomListener(instance, "event_name", handle, true);'.PHP_EOL.
            'var '.$domEventOnce->getJavascriptVariable().' = google.maps.event.addDomListenerOnce(instance, "event_name", handle, true);'.PHP_EOL.
            'var '.$event->getJavascriptVariable().' = google.maps.event.addListener(instance, "event_name", handle);'.PHP_EOL.
            'var '.$eventOnce->getJavascriptVariable().' = google.maps.event.addListenerOnce(instance, "event_name", handle);'.PHP_EOL
        );
    }
}
