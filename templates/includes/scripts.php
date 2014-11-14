         <script src="/media/js/jquery-1.11.0.js"></script>

	<script type="text/javascript"
            src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

        <script src="/media/js/bootstrap.min.js"></script>

	<script type="text/javascript" src="/media/extjs/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="/media/extjs/ext-all.js"></script>

 	<!------------------------------------>
        <!-- Script for loading             -->
        <!------------------------------------>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
	<script type="text/javascript" src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
	<script type="text/javascript" src="/media/myjs/dateRangePicker.js"></script>

 	<!------------------------------------>
        <!--		My SCRIPTS            -->
        <!------------------------------------>
	<script type="text/javascript" src="media/myjs/get_colorbar.js"></script>
	<script type="text/javascript" src="media/myjs/formListener.js"></script>

 	<!------------------------------------>
        <!-- Script for progress bar        -->
        <!------------------------------------>
	<script type="text/javascript" src="media/myjs/showLoadingImage.js"></script>

 	<!------------------------------------>
        <!-- Script for google map toolbar  -->
        <!------------------------------------>
	
	<script type="text/javascript" src="media/myjs/mapToolbar.js"></script>

 	<!------------------------------------>
        <!-- Script for zooming to state level-->
	<!-- from view-source:http://geocodezip.com/v3_zoom2stateselectlist.html -->
        <!------------------------------------>
	 <script type="text/javascript">
		function findAddress(address) {
		  var addressStr=document.getElementById("stateselect").value;
		  if (!address && (addressStr != '')) 
		     address = "State of "+addressStr;
		  else 
		     address = addressStr;
		  if ((address != '') && geocoder) {
		   geocoder.geocode( { 'address': address}, function(results, status) {
		   if (status == google.maps.GeocoderStatus.OK) {
		     if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
		       if (results && results[0]
			   && results[0].geometry && results[0].geometry.viewport) 
			 map.fitBounds(results[0].geometry.viewport);
		     } else {
		       alert("No results found");
		     }
		   } else {
		    alert("Geocode was not successful for the following reason: " + status);
		   }
		   });
		  }
		}
	</script>

  	<!------------------------------------>
        <!-- Google Earth Map -->
        <!------------------------------------>
	 <script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
	 <script type="text/javascript">
 	      var MAPID = "{{ mapid }}";
	      var TOKEN = "{{ token }}";

	      var eeMapOptions = {
			getTileUrl: function(tile, zoom) {
				  var url = ['https://earthengine.googleapis.com/map',
					     MAPID, zoom, tile.x, tile.y].join("/");
				  url += '?token=' + TOKEN
				  return url;
			},
			tileSize: new google.maps.Size(256, 256)
	      };
	      var mapType = new google.maps.ImageMapType(eeMapOptions);
	      /*********************************
	      *    INITIALIZE CALL
	      *********************************/
	      var map=null;
	      var pointmarker = null;
	      var statemarkerLayer = null;
	
	      function initialize() {
		var myCenter = new google.maps.LatLng({{ pointLat }}, {{ pointLong }});
		var myZoom ={{ mapzoom }}
		var mapOptions = {
		  center: myCenter,
		  zoom: myZoom,
		  maxZoom: 10,
		  streetViewControl: false,
		  //mapTypeId: google.maps.MapTypeId.ROADMAP,
                  mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
		  clickable:true,
		};
		/*geocoder = new google.maps.Geocoder();
                findAddress("United States");*/

		window.map = new google.maps.Map(document.getElementById("map"),mapOptions);

		
		/*********************************
		*      MULTIPLE POINTS OR POLYGON DRAWING FEATURE                   *
		*********************************/
		/*
		with(MapToolbar){
		    with(buttons){
					$hand = document.getElementById("hand_b");
					$shape = document.getElementById("shape_b");
					$line = document.getElementById("line_b");
					$placemark = document.getElementById("placemark_b");
		    }
		    $featureTable = document.getElementById("featuretbody");
		    select("hand_b");
		}
		
		MapToolbar.polyClickEvent = google.maps.event.addListener(map, 'click',  function(event){
		if( !MapToolbar.isSelected(MapToolbar.buttons.$shape) && !MapToolbar.isSelected(MapToolbar.buttons.$line) ) return;
		    if(MapToolbar.currentFeature){
			    MapToolbar.addPoint(event, MapToolbar.currentFeature);
		    }
		});
		*/

		/*********************************
		*      POINTS                    *
		*********************************/
		window.pointmarker = new google.maps.Marker({position:new google.maps.LatLng({{ pointLat }},{{ pointLong }}),
			     map: map, draggable: true});
		google.maps.event.addListener(window.pointmarker, 'dragend', function(a) {
			  var div = document.createElement('div');
			  var longitude=a.latLng.lng().toFixed(4)
			  var latitude=a.latLng.lat().toFixed(4)
			  document.getElementById('pointLatLong').value = longitude+','+latitude;
		});
		window.pointmarker.setVisible(false); 

		/*********************************
		*      STATES                    *
		*********************************/
		window.statemarkerLayer = new google.maps.KmlLayer('http://www.wrcc.dri.edu/monitor/WWDT/KML/states.kmz', {
                map:map,
                    preserveViewport: true,
                    suppressInfoWindows: false
                 }); //end KmlLayer
		 google.maps.event.addListener(statemarkerLayer, 'click', function(kmlEvent) {
			alert(kmlEvent.featureData.id);
                        $('#states').val(kmlEvent.featureData.id);
	
                //}
          	}); //end listener
		/*********************************
		*      POLYGON                    *
		*********************************/

		/*********************************
		*      MULTIPLE POINTS           *
		*********************************/

		window.statemarkerLayer.setMap(null);

		/*********************************/
		window.map.overlayMapTypes.push(mapType);
	      }
	      google.maps.event.addDomListener(window, 'load', initialize);
	      window.onload = initialize;
	</script>

 	<!------------------------------------>
        <!-- Script for charts            -->
        <!------------------------------------>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript" src="/media/myjs/graph_utils.js"></script>
 	<!------------------------------------>
        <!-- Script for charts            -->
        <!------------------------------------>
        <script src="http://d3js.org/d3.v3.min.js"></script>

	 <!--<script type="text/javascript" src="media/myjs/d3Example.js"></script>-->
	<script type="text/javascript">
		 //de = {{ timeSeriesGraphData }};
		de=[{'name': '01/18/2013', 'count': 0.16711192997972898}, {'name': '01/25/2013', 'count': 0.1506114002764564}, {'name': '02/03/2013', 'count': 0.17008802964192737}, {'name': '02/10/2013', 'count': 0.05392157351396518}, {'name': '02/26/2013', 'count': 0.16506960912310953}, {'name': '03/07/2013', 'count': 0.1948166408371296}, {'name': '03/14/2013', 'count': 0.1671125205954915}, {'name': '03/30/2013', 'count': 0.29349505796330344}];
		  //de=[{'count': 728, 'name': 'sample0'}, {'count': 824, 'name': 'sample1'}, {'count': 963, 'name': 'sample2'}, {'count': 927, 'name': 'sample3'}, {'count': 221, 'name': 'sample4'}, {'count': 574, 'name': 'sample5'}, {'count': 733, 'name': 'sample6'}, {'count': 257, 'name': 'sample7'}, {'count': 879, 'name': 'sample8'}, {'count': 620, 'name': 'sample9'}];

    var mySVG = d3.select("#d3bargraph_div")
      .append("svg")
      .attr("width", 500) 
      .attr("height", 500)
      .style('position','absolute')
      .style('top',50)
      .style('left',40)
      .attr('class','fig');

    var heightScale = d3.scale.linear()
      .domain([0, d3.max(de,function(d) { return d.count;})])
      .range([0, 400]);

    mySVG.selectAll(".xLabel")
      .data(de)
      .enter().append("svg:text")
      .attr("x", function(d,i) {return 113 + (i * 22);})
      .attr("y", 435)
      .attr("text-anchor", "middle") 
      .text(function(d,i) {return d.name;})
      .attr('transform',function(d,i) {return 'rotate(-90,' + (113 + (i * 22)) + ',435)';}); 

	mySVG.append("text")
    .attr("class", "x label")
    .attr("text-anchor", "end")
    .text("Date");


    mySVG.selectAll(".yLabel")
      .data(heightScale.ticks(10))
      .enter().append("svg:text")
      .attr('x',80)
      .attr('y',function(d) {return 400 - heightScale(d);})
      .attr("text-anchor", "end") 
      .text(function(d) {return d;}); 

	mySVG.append("text")
    .attr("class", "y label")
    .attr("text-anchor", "end")
    .attr("transform", "rotate(-90)")
    .text("NDVI");


    mySVG.selectAll(".yTicks")
      .data(heightScale.ticks(10))
      .enter().append("svg:line")
      .attr('x1','90')
      .attr('y1',function(d) {return 400 - heightScale(d);})
      .attr('x2',320)
      .attr('y2',function(d) {return 400 - heightScale(d);})
      .style('stroke','lightgray'); 

    var myBars = mySVG.selectAll('rect')
      .data(de)
      .enter()
      .append('svg:rect')
      .attr('width',20)
      .attr('height',function(d,i) {return heightScale(d.count);})
      .attr('x',function(d,i) {return (i * 22) + 100;})
      .attr('y',function(d,i) {return 400 - heightScale(d.count);})
      .style('fill','lightblue')
      .on('mouseover', function(d,i) { 
         d3.select(this)
            .style('fill','gray'); 
         statusText 
            .text(d.count)
            .attr('fill','white')
            .attr("text-anchor", "start") 
            .attr("x", (i * 22) + 105) 
            .attr("y", 414) 
            .attr('transform', 'rotate(-90,' + (100 + (i * 22)) + ',400)'); }) 
      .on('mouseout', function(d,i) { 
         statusText 
           .text(''); 
         d3.select(this)
           .style('fill','lightblue'); 
      }); 
   var statusText = mySVG.append('svg:text');


      </script>
 <script type="text/javascript">
                data=[{'name': '01/18/2013', 'count': 0.16711192997972898}, {'name': '01/25/2013', 'count': 0.1506114002764564}, {'name': '02/03/2013', 'count': 0.17008802964192737}, {'name': '02/10/2013', 'count': 0.05392157351396518}, {'name': '02/26/2013', 'count': 0.16506960912310953}, {'name': '03/07/2013', 'count': 0.1948166408371296}, {'name': '03/14/2013', 'count': 0.1671125205954915}, {'name': '03/30/2013', 'count': 0.29349505796330344}];


var margin = {top: 70, right: 70, bottom: 70, left: 70},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

var parseDate = d3.time.format("%d-%b-%y").parse;

var x = d3.time.scale()
.range([0, width]);

var y = d3.scale.linear()
.range([height, 0]);

var xAxis = d3.svg.axis()
.scale(x)
.orient("bottom");

var yAxis = d3.svg.axis()
.scale(y)
.orient("left");

var line = d3.svg.line()
.x(function(d) { return x(d.date); })
.y(function(d) { return y(d.close); });

var svg = d3.select("d3linegraph_div").append("svg")
.attr("width", width + margin.left + margin.right)
.attr("height", height + margin.top + margin.bottom)
.append("g")
.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

data.forEach(function(d) {
    d.date = parseDate(d.date);
    d.close = d.close;
});

console.log(data);

x.domain(d3.extent(data, function(d) { return d.date; }));
y.domain(d3.extent(data, function(d) { return d.close; }));

//Create Title 
svg.append("text")
.attr("x", width / 2 )
.attr("y", -10)
.attr("class", "title")
.style("text-anchor", "middle")
.style("font-size", "200%")
.text("Title of Diagram");



//Create X Axis
svg.append("g")
.attr("class", "x axis")
.attr("transform", "translate(0," + height + ")")
.call(xAxis)
.append("text")
.attr("x", width / 2 )
.attr("y", margin.bottom / 2)
.style("text-anchor", "middle")
.style("font-size", "150%")
.text("Date");

//Create Y Axis		
svg.append("g")
.attr("class", "y axis")
.call(yAxis)
.append("text")
.attr("transform", "rotate(-90)")
.attr("y", 6)
.attr("y", - margin.left / 2)
.attr("x", - height / 2)
.style("text-anchor", "middle")
.style("font-size", "150%")
.text("Close");

svg.append("path")
.datum(data)
.attr("class", "line")
.attr("d", line);

// add circles
svg.selectAll('circle')
.data(data)
.enter().append('circle')
.attr('cx', function (d) { return x(d.date); })
.attr('cy', function (d) { return y(d.close); })
.attr('r', 3)
.attr('fill', 'red'); 

// add rectangle for label and line
var infos = svg.append("g")
.selectAll("rect")
.data(data)
.enter()
.append("g")
.attr("transform",function(d,i){d.x = x(d.date); d.y = height/2; return "translate(" + d.x + "," + d.y + ")";})
	



</script>