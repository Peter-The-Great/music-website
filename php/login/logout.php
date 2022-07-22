<?php
//hier zorgen we ervoor dat we uitgelogd worden.
    session_start();
    session_abort();
    session_destroy();
    header("Location: ../../index.php");
?>