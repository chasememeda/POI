var map = new VEMap('myMap');

var myLocation = new VELatLong(31.0426,121.411, 0, VEAltitudeMode.Default);
map.LoadMap(myLocation, 10, VEMapStyle.Road, false, VEMapMode.Mode2D, false, 1);
map.AttachEvent("onclick", PixelClick);
showLocationInfo( myLocation );
var myLocationPin = new VEShape(VEShapeType.Pushpin, myLocation);
addMyPushpin();


var rsultLayer = new VEShapeLayer();
function deleteShaplayer(){
	map.DeleteAllShapeLayers();
	rsultLayer = new VEShapeLayer();
	myLocationPin = new VEShape(VEShapeType.Pushpin, myLocation)
	rsultLayer.AddShape(myLocationPin);
}
function addShap( shape ){
	rsultLayer.AddShape( shape ); 
}
function showResult(){
	map.AddShapeLayer(rsultLayer);
}

function resetCenter(){
	setCenterTo(myLocation);
}

function setCenterTo( location ){
	map.SetCenter(location);
}

function PixelClick(e)
 {	if($('input[name="locationSelect"]').attr("locate") == "true"){
		var x = e.mapX;
		var y = e.mapY;
		pixel = new VEPixel(x, y);
		var LL = map.PixelToLatLong(pixel);
		$("#info").html( "Pixel X: " + x + " | Pixel Y: " + y + "<br /> LatLong: " + LL);
		deleteMyPushpin();
		myLocation = LL;
		showLocationInfo( LL );
		addMyPushpin();
	}
 }
function addPushpin(location , title, descript ){
	 var myPolygon = new VEShape(VEShapeType.Pushpin, location);
	 var myPolygon = map.AddShape(myPolygon);
	 myPolygon.SetTitle(title);
	 myPolygon.SetDescription(descript);
}
function addMyPushpin(){
	myLocationPin = new VEShape(VEShapeType.Pushpin, myLocation);
	map.AddShape(myLocationPin);
}
function deleteMyPushpin(){
	map.DeleteShape(myLocationPin);
}
function showLocationInfo( ll ){
	$("#info").html( "LatLong:" + ll.Latitude.toFixed(3) + "," + ll.Longitude.toFixed(3) );
}
function getLat(){
	return myLocation.Latitude;
}

function getLong(){
	return myLocation.Longitude;
}