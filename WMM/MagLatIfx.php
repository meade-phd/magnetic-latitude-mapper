<?php

// MagLatIfx.php acts as an intermediary between the Magnetic Latitude webpage
// JavaScript and the MagLatCalc executable that actually calculates magnetic
// latitude from geographic latitude and longitude. This "shim" is necessary
// because there is no simple and easy way for the executable itself to parse
// the geographic lat and lon, the elevation, and the date from a URL. So this
// script extracts those arguments, passes them to an execution of MagLatCalc,
// and passes the calculated magnetic latitude back to the calling web page
// through the simple expedient of echoing the calculated value (or an error
// message).

// Extract the lat, lon, elev, and date parameters passed from the web page:
$lat  = escapeshellcmd($_GET['lat']);
$lon  = escapeshellcmd($_GET['lon']);
$elev = escapeshellcmd($_GET['elev']);
$date = escapeshellcmd($_GET['date']);

// Replace the _ in the date string with /. The _ are needed
// for passing the date in the URL, but the / are needed for
// the argument to MagLatCalc.
for ($i = 0; $i < strlen($date); $i++) {
    if ($date[$i] == '_') $date[$i] = '/';
}


// Construct and execute the MagLatCalc command:
//   Note: MagLatCalc is the renamed WMM wmm_ptcli.
$cmd  = "./MagLatCalc " . $lat . " " . $lon . " " . $elev . " " . $date . " " . "oneline";
$maglat = exec($cmd);

// If MagLatCalc returned a valid response (a string starting with "MagLat:",
// extract the magnetic latitude and return it to the web page by printing
// it using 'echo'. If not, return the string "Error!" to the web page:
//   ToDo: Handle errors or other messages printed by MagLatCalc.
if ($maglatpos = strpos($maglat, 'gLat:')) {
   $beg = $maglatpos + 5;
   if ($end = strpos($maglat, '|')) {
      echo substr($maglat, $beg, ($end - $beg));
   }

   else {
      echo substr($maglat, $beg);
   }
}

else {
   echo 'Error!';
}

?>

