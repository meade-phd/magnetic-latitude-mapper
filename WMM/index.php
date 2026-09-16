<!DOCTYPE html>
<!-- This page loads a default google maps view, centered on Lebanon, KS (near the   -->
<!-- geographical center of "the lower 48" United States). It allows the user to pan -->
<!-- around, zoom, etc. When the user clicks on a location, the page calls the       -->
<!-- server-side MagLatIfx.php script, which returns the magnetic latitude of a      -->
<!-- location. The page then pops up an InfoWindow which displays the geographical   -->
<!-- latitude and longitude and the magnetic latitude of the clicked location.       -->
<html>
  <head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <title>Magnetic Latitude</title>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      .custom-infowindow {
        /* font-size: 24px;        Change text size */
        font-size: 14px;       /* Change text size */
        font-family: Arial, sans-serif;
        color: #222;
        /* line-height: 1.5; */
        line-height: 1.2;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <script>

      // When the web page is loaded, initMap() is called to set up the map:
      async function initMap() {

        // Load required libraries:
        const { Map } = await google.maps.importLibrary("maps");
        const { ElevationService } = await google.maps.importLibrary("elevation");


        // Create and display the initial map:
        var start_loc = new google.maps.LatLng( 39.8333,-98.5833);  // Lebanon, KS
        var map = new Map(document.getElementById('map'), {
          zoom: 6,
          center: start_loc
        });

        // Initial Info Window provides instructions:
        var infoWindow = new google.maps.InfoWindow();
        infoWindow.setContent('<div class="custom-infowindow">' +
                              ['Magnetic Latitude Calculator',
                               '',
                               '1) Pan and zoom to desired location',
                               '2) Click to display magnetic latitude'].join('<br>') +
                              '</div>');
	infoWindow.setPosition(start_loc);
	infoWindow.open(map);

        // Will need an ElevationService object to retrieve elevations;
        const elevator = new ElevationService();

        // When a map location is clicked, tagLatLonMagLat() is called:
        map.addListener('click', function(e) {
          tagLatLonMagLat(e.latLng, elevator, infoWindow, map);
          });
      }

      async function tagLatLonMagLat(latLng, elevator, infoWindow, map) {

        var magLat = 'Unknown';
        var elev   = 'Unknown';
        var now    = new Date();
        var mm     = 1 + now.getMonth();
        var dd     =     now.getDate();
        var yyyy   =     now.getFullYear();


        // Determine the surface elevation at latLng:
        try {
          const { results } = await elevator.getElevationForLocations({ locations: [latLng] });

          if (results && results[0]) {
            elev = (results[0].elevation/1000.0).toFixed(2);
            if (elev < 0.0) elev = "0.00";
          }
          else {
            console.error("Failed to retrieve elevation data.");
          }
        }
        catch (error) {
          console.error("Elevation service failed due to: " + error);
        }


        // AJAX call to MagLatIfx.php to get magnetic latitude. MagLatIfx.php
        // uses the MagLatCalc executable to do the calculation:
        $.ajax({url: "MagLatIfx.php?" +
                     "lat="   + (Math.round(latLng.lat()*10000)/10000) + 
                     "&lon="  + (Math.round(latLng.lng()*10000)/10000) +
                     "&elev=" + elev +
                     "&date=" + mm + "_" + dd + "_" + yyyy,
                     success: function(result){

          magLat = result;

          // Replace the infoWindow:
          infoWindow.close();

          infoWindow.setContent('<div class="custom-infowindow">' +
                                ['Latitude:  ' + (Math.round(latLng.lat()*100)/100.0),
                                 'Longitude: ' + (Math.round(latLng.lng()*100)/100.0),
                                 'Elevation (km): ' + elev,
                                 'Magnetic Latitude: ' + magLat,
                                ].join('<br>') +
                                '</div>');

          infoWindow.setPosition(latLng);
          infoWindow.open(map);
        }});

      }
    </script>

    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=<Google Maps API Key>&callback=initMap">
    </script>
  </body>
</html>

