<?php
// kudos to http://stackoverflow.com/a/18106727/1778785 for snippet of PHP to read Google spreadsheet as CSV

$googleSpreadsheetUrl = "https://docs.google.com/spreadsheets/d/1NPvtZB9LLrUa8SCuwFcf0Q0zwCIzRzU11pNyAoIO_MU/pub?output=csv";

$rowCount = 0;
$features = array();
$error = FALSE;
$output = array();

// attempt to set the socket timeout, if it fails then echo an error
if ( ! ini_set('default_socket_timeout', 15))
{
  $output = array('error' => 'Unable to Change PHP Socket Timeout');
  $error = TRUE;
} // end if, set socket timeout

// if the opening the CSV file handler does not fail
if ( !$error && (($handle = fopen($googleSpreadsheetUrl, "r")) !== FALSE) )
{
  // while CSV has data, read up to 10000 rows
  while (($csvRow = fgetcsv($handle, 10000, ",")) !== FALSE)
  {
    $rowCount++;


    if ($rowCount == 1) { continue; } // skip the first/header row of the CSV


      /*
      // Sonderbehandlung der Spalte coordinates
      $coordinatesColumn = $csvRow[5];

      // lt/lng in jeweils einen Eintrag pro Zeile trennen
      $coordinatesColumn1 = str_replace('], [',"\n", $coordinatesColumn);
      $coordinatesColumn2 = str_replace('[','', $coordinatesColumn1);
      $coordinatesColumn3 = str_replace(', ',',', $coordinatesColumn2);
      $coordinatesColumn4 = str_replace(']','', $coordinatesColumn3);
      //print_r($coordinatesColumn4);

        // aus jeder Zeile ein array mit key/value lat/lng
      $testarray = array();
      foreach (explode("\n", $coordinatesColumn4) as $cLine) {
          list ($cKey, $cValue) = explode(',', $cLine, 2);
          $testarray[$cKey] = $cValue;
      }
      // here is the magic: vertausche key/value <->value/key
      $testarray = array_flip($testarray);

        // jetzt wieder zurrrrück das ganze: flatte das (hilfs)array
      foreach ($testarray as $key => $value) {
        $list[] = "[$key, $value]";e;
      }
      //echo 'The values are: '.implode(', ',$list) . "<hr>";
        // füge die einträge aus jeder zeile in EIN value
      $testarray = implode(', ',$list);
      //print_r($testarray);
      */

      $features[] = array(
      'type'     => 'Feature',
      'geometry' => array(
         /*
        'type'   => 'Point',
        'coordinates' => array(
          (float) $csvRow[1], // longitude, casted to type float
          (float) $csvRow[0]  // latitude, casted to type float
       	 */
        'type'   => 'Polygon', // https://geojson.org/geojson-spec.html#geometry-objects
        'coordinates' => array(
            //$testarray  // muss das gecastet werden?
            $csvRow[3]
           // TEST: siehe raw output von http://jw-heide.org/geb1/geojson.php
       )

    // TODO: flip lat-lng
    // export der values in ein array, key ist lat value lng
    // dann array_flip
    // dann array wieder zurück ins hauptarray
    //$features[0][geometry][coordinates][0]

      ),
      'properties' => array(
        'title' => $csvRow[0],
        'notes' => $csvRow[1],
        'status' => $csvRow[2]
      )
    );



  } // end while, loop through CSV data

  fclose($handle); // close the CSV file handler

  $output = array(
    'type' => 'FeatureCollection',
    'features' => $features
  );
}  // end if , read file handler opened

// else, file didn't open for reading$pieces
else
{
  $output = array('error' => 'Problem Reading Google CSV');
}  // end else, file open fail

// convert the PHP output array to JSON "pretty" format
$jsonOutput = json_encode($output, JSON_PRETTY_PRINT);

// nicht schoen aber wirkungsvoll - manuelles verbiegen des Outputs
//$jsonOutput1 = str_replace('coordinates": {', 'coordinates": [', $jsonOutput);
//$jsonOutput2 = str_replace('                }', '                ]', $jsonOutput1);
//$jsonOutput3 = str_replace('"[', '[', $jsonOutput2);
//$jsonOutput4 = str_replace('": 0', '', $jsonOutput3);
//$jsonOutput5 = str_replace(']"', ']', $jsonOutput4);

$jsonOutput = str_replace('"[', '[ [', $jsonOutput);
$jsonOutput = str_replace(']"', '] ]', $jsonOutput);

// render JSON and no cache headers
header('Content-type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Access-Control-Allow-Origin: *');



//print_r($testarray);
//$testarray_flipped = array_flip($testarray);
//print_r($testarray_flipped);

// jetzt wieder zerlegen in einer schleife in [key, value], newline etc..

print $jsonOutput;
//print_r($output);
