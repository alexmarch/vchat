<?php
if ( isset ( $GLOBALS["HTTP_RAW_POST_DATA"] )) {
    $uniqueStamp = date(U);
    $filename = $uniqueStamp.".jpg";
    $fp = fopen('/tmp/snapshots/'.$filename,"wb");
    fwrite( $fp, $GLOBALS[ 'HTTP_RAW_POST_DATA' ] );
    fclose( $fp );
    echo "filename=".$filename."&base=".$_SERVER["HTTP_HOST"].dirname($_SERVER["PHP_SELF"]);
}
?>