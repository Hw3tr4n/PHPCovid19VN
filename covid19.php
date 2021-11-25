<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Viet Nam Covid-19 Tracking</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css" />
        <script src="https://openlayers.org/en/v4.6.5/build/ol.js" type="text/javascript"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
        <style>
            th { background-color: #bde9ba; }
            
            .popover-body {
            min-width: 276px;
             }
            .container{
            padding: 1rem;
            margin: 1rem;
            }
            #layertree li > span {
            cursor: pointer;
            }
            #layertree label {
            display: block;
            }
            .center {
            text-align: center;
            color: red;
            font-weight: bold;
            }
            /*
            .map, .righ-panel {
                height: 500px;
                width: 80%;
                float: left;
            }
            */
            .map, .righ-panel {
                height: 100vh;
                width: 60vw;
                float: left;
            }
           
            .ol-popup {
                position: absolute;
                background-color: white;
                box-shadow: 0 1px 4px rgba(0,0,0,0.2);
                padding: 10px;
                border-radius: 10px;
                border: 1px solid #cccccc;
                bottom: 12px;
                left: -50px;
                min-width: 150px;
            }
            .ol-popup:after, .ol-popup:before {
                top: 100%;
                border: solid transparent;
                content: " ";
                height: 0;
                width: 0;
                position: absolute;
                pointer-events: none;
            }
            .ol-popup:after {
                border-top-color: white;
                border-width: 10px;
                left: 48px;
                margin-left: -10px;
            }
            .ol-popup:before {
                border-top-color: #cccccc;
                border-width: 11px;
                left: 48px;
                margin-left: -11px;
            }
            .ol-popup-closer {
                text-decoration: none;
                position: absolute;
                top: 2px;
                right: 3px;
            }
            .ol-popup-closer:after {
                content: "✖";
            }
        </style>
    </head>
    <body onload="initialize_map(); "> 
        <div id="popup" class="ol-popup">
            <a href="#" id="popup-closer" class="ol-popup-closer"></a> 
            <div id="popup-content"></div>    
        </div>
        <table>
            <tr>
                <td>
                    <div id="map" class="map"> </div>
                    <!--<div id="map" style="width: 80vw; height: 100vh;"></div>-->
                </td>
                <td>
                
                <div id="layertree">
                <h4>Bật/Tắt layer trên bản đồ</h4>
                <ul>
                    <li>
                    <span>Covid layers</span>
                    <fieldset id="layer0">
                        <span style = "font-weight: bold; ">Layer tình hình dịch bệnh theo tỉnh</span>
                        <label class="checkbox" for="visible1">
                        Hiển thị <input id="visible1" class="visible" type="checkbox"/>
                        </label>
                        <label>
                        Độ mờ <input class="opacity" type="range" min="0" max="1" step="0.01"/>
                        </label>
                    </fieldset>
                    <fieldset id="layer1">
                        <span style = "font-weight: bold; ">Layer các chốt, trung tâm xét nghiệm covid</span>
                        <label class="checkbox" for="visible1">
                        Hiển thị <input id="visible1" class="visible" type="checkbox"/>
                        </label>
                        <label>
                        Độ mờ <input class="opacity" type="range" min="0" max="1" step="0.01"/>
                        </label>
                    </fieldset>
                    <fieldset id="layer2">
                        <span style = "font-weight: bold; ">Layer địa điểm các cây ATM gạo, siêu thị 0 đồng</span>
                        <label class="checkbox" for="visible1">
                        Hiển thị <input id="visible1" class="visible" type="checkbox"/>
                        </label>
                        <label>
                        Độ mờ <input class="opacity" type="range" min="0" max="1" step="0.01"/>
                        </label>
                    </fieldset>
                    </li>
                </ul>
                </div>

                <?php include 'showtotal.php' ?>
                <p></p>
                <?php include 'showmaxInfected.php' ?>
                <p></p>
                <?php include 'showmaxDead.php' ?>
                <p></p>
                <span  style = "font-weight: bold; color:#2530F3;font-size:25px;">Thông tin điểm được chọn</span>
                <div id="info"></div>
                <p style = "font-weight: bold; height: -10px">Ký hiệu bản đồ (đơn vị: số ca)</p>
                <img src='mapsyms.png' style = height:110px> 
                </td>
                </table>
            </tr>
        </table>
        <?php include 'pgsqlAPI.php' ?>
        <script>
        //$("#document").ready(function () {
            var format = 'image/png';
            var map;
            var minX = 102.144584655762;
            var minY = 8.38135528564453;
            var maxX = 109.469177246094;
            var maxY = 23.3926944732666;
            var cenX = (minX + maxX) / 2;
            var cenY = (minY + maxY) / 2;
            var mapLat = cenY;
            var mapLng = cenX;
            var mapDefaultZoom = 5.8;
            var container = document.getElementById('popup');
            var content = document.getElementById('popup-content');
            var closer = document.getElementById('popup-closer');
            
                /**
                 * Create an overlay to anchor the popup to the map.
                 */
                var overlay = new ol.Overlay(({
                    element: container,
                    autoPan: true,
                    autoPanAnimation: {
                    duration: 250
                    }
                    }));

                /**
                 * Add a click handler to hide the popup.
                 *  Don't follow the href.
                 */
                closer.onclick = function () {
                    overlay.setPosition(undefined);
                    closer.blur();
                    return false;
                };
            function initialize_map() {
                var styles = {
                    'Point': [new ol.style.Style({
                        image: new ol.style.Circle({
                            radius: 6,
                            fill: new ol.style.Fill({
                                color: [255, 255, 255, 0.3]
                            }),
                            stroke: new ol.style.Stroke({color: '#2530F3', width: 2})
                        })
                    })],
                    'LineString': [new ol.style.Style({
                        stroke: new ol.style.Stroke({
                            color: 'green',
                            width: 1
                        })
                    })],
                    'Polygon': [new ol.style.Style({
                        stroke: new ol.style.Stroke({
                            color: 'blue',
                            lineDash: [4],
                            width: 3
                        }),
                        fill: new ol.style.Fill({
                            color: 'rgba(0, 0, 255, 0.1)'
                        })
                    })],
                    'Circle': [new ol.style.Style({
                        stroke: new ol.style.Stroke({
                            color: 'red',
                            width: 2
                        }),
                        fill: new ol.style.Fill({
                            color: 'rgba(255,0,0,0.2)'
                        })
                    })],
                    'MultiPolygon': new ol.style.Style({    
                        stroke: new ol.style.Stroke({
                            color: 'yellow', 
                            width: 3
                        })
                    })
                };
                var styleFunction = function (feature) {
                    return styles[feature.getGeometry().getType()];
                };
                var vectorLayer = new ol.layer.Vector({
                    //source: vectorSource,
                    style: styleFunction
                });
                //*
                layerBG = new ol.layer.Tile({
                    source: new ol.source.OSM({})
                });
                //*/
                layerGoogle = new ol.layer.Tile({
                    'title': 'Google Maps Uydu',
                    'type': 'base',
                    visible: true,
                    'opacity': 1.000000,
                    source: new ol.source.XYZ({
                    attributions: [new ol.Attribution({ html: '<a href=""></a>' })],
                    url: 'http://mt0.google.com/vt/lyrs=y&hl=en&x={x}&y={y}&z={z}&s=Ga'
                    })
                });
                var layer_VN = new ol.layer.Image({
                    source: new ol.source.ImageWMS({
                        ratio: 1,
                        url: 'http://localhost:8080/geoserver/test/wms?',
                        params: {
                            'FORMAT': format,
                            'VERSION': '1.1.1',
                            STYLES: 'Provinces',
                            LAYERS: 'provinces',
                        }
                    })
                });

                var layer_VNpoints = new ol.layer.Image({
                    source: new ol.source.ImageWMS({
                        ratio: 1,
                        url: 'http://localhost:8080/geoserver/test/wms?',
                        params: {
                            'FORMAT': format,
                            'VERSION': '1.1.1',
                            STYLES: 'stationcovid',
                            LAYERS: 'covidstation',
                        }
                    })
                });
                var viewMap = new ol.View({
                    center: ol.proj.fromLonLat([mapLng, mapLat]),
                    zoom: mapDefaultZoom
                    //projection: projection
                });
                var layer_VNatm = new ol.layer.Image({
                    source: new ol.source.ImageWMS({
                        ratio: 1,
                        url: 'http://localhost:8080/geoserver/test/wms?',
                        params: {
                            'FORMAT': format,
                            'VERSION': '1.1.1',
                            STYLES: 'atm',
                            LAYERS: 'atmrice',
                        }
                    })
                });
                var Stationcovid_geojson_layer = new ol.layer.Vector({
                    renderMode: 'image',
                    style: styleFunction,
                    source: new ol.source.Vector({
                    url: 'covid19station.geojson',
                    format: new ol.format.GeoJSON()
                    }),
                    
                });
                var Atmrice_geojson_layer = new ol.layer.Vector({
                    renderMode: 'image',
                    style: styleFunction,
                    source: new ol.source.Vector({
                    url: 'atmricevn.geojson',
                    format: new ol.format.GeoJSON()
                    }),
                    
                });
                var provinces_geojson_layer = new ol.layer.Vector({
                    renderMode: 'image',
                    style: styleFunction,
                    source: new ol.source.Vector({
                    url: 'provincesvn.geojson',
                    format: new ol.format.GeoJSON()
                    }),
                    
                });
                var viewMap = new ol.View({
                    center: ol.proj.fromLonLat([mapLng, mapLat]),
                    zoom: mapDefaultZoom
                    //projection: projection
                });
                
                map = new ol.Map({
                    target: "map",
                    layers: [layer_VN,Stationcovid_geojson_layer, Atmrice_geojson_layer],
                    overlays: [overlay],
                    //layers: [layerCMR_adm1],
                    view: viewMap
                });
                
                
                map.addLayer(vectorLayer);
                function createJsonObj(result) {                    
                    var geojsonObject = '{'
                            + '"type": "FeatureCollection",'
                            + '"crs": {'
                                + '"type": "name",'
                                + '"properties": {'
                                    + '"name": "EPSG:4326"'
                                + '}'
                            + '},'
                            + '"features": [{'
                                + '"type": "Feature",'
                                + '"geometry": ' + result
                            + '}]'
                        + '}';
                    return geojsonObject;
                }
            
                function bindInputs(layerid, layer) {
                    const visibilityInput = $(layerid + ' input.visible');
                    visibilityInput.on('change', function () {
                        layer.setVisible(this.checked);
                    });
                    visibilityInput.prop('checked', layer.getVisible());

                    const opacityInput = $(layerid + ' input.opacity');
                    opacityInput.on('input', function () {
                        layer.setOpacity(parseFloat(this.value));
                    });
                    opacityInput.val(String(layer.getOpacity()));
                }
                function setup(id, group) {
                    group.getLayers().forEach(function (layer, i) {
                        const layerid = id + i;
                        bindInputs(layerid, layer);
                    });
                }
                setup('#layer', map.getLayerGroup());

                $('#layertree li > span')
                .click(function () {
                    $(this).siblings('fieldset').toggle();
                })
                .siblings('fieldset')
                .hide();
                
                map.on('click', function (evt) {
                    //alert("coordinate org: " + evt.coordinate);
                    //var myPoint = 'POINT(12,5)';
                    var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                    var lon = lonlat[0];
                    var lat = lonlat[1];
                    var myPoint = 'POINT(' + lon + ' ' + lat + ')';
 
                    //alert("myPoint: " + myPoint);
                    //*
                    $.ajax({
                        type: "POST",
                        url: "pgsqlAPI.php",
                        //dataType: 'json',
                        data: {functionname: 'getInfoAjax', paPoint: myPoint},
                        success : function (result, status, erro) {
                                $("#popup-content").html(result);
                            overlay.setPosition(evt.coordinate);
                           // displayObjInfo(result, evt.coordinate );
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                    

                });
                map.on('click', function(evt){
                    var feature = map.forEachFeatureAtPixel(evt.pixel,
                        function(feature, layer){
                            return feature;
                        });
                    if (feature) { 
                        var geometry = feature.getGeometry();
                        var coord = geometry.getCoordinates();
                        
                        var content = '<tr><th>'+ feature.get('Type')+'</th></tr><tr><td>' + feature.get('Location') + '</td>';
                        
                        $("#info").html(content);
                        overlay.setPosition(coord);
                        
                        console.info(feature.getProperties());
                    }
                }); 
               map.on("pointermove", function (evt) {
                    var hit = this.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
                        return true;
                    }); 
                    if (hit) {
                        this.getTargetElement().style.cursor = 'pointer';

                    } else {
                        this.getTargetElement().style.cursor = '';
                    }
                });
             };
        //});
        </script>
    </body>
</html>