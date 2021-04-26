var Maps = function () {
    //function to initiate GMaps
    //Gmaps.js allows you to use the potential of Google Maps in a simple way.
    //For more information, please visit http://hpneo.github.io/gmaps/documentation.html
    var runMaps = function () {
        // Basic Map
        map = new GMaps({
            el: '#text-map',
            zoom: map_zoom,
            lat: map_location[0],
            lng: map_location[1]
        });
        map.addMarker({
            lat: map_location[0],
            lng: map_location[1],
            title: 'Help Location',
            infoWindow: {
                content: '<p>Help Location</p>'
              },
          });
    };
    return {
        //main function to initiate template pages
        init: function () {
            runMaps();
        }
    };
}();
