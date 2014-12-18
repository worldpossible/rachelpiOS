<?php

    $stats = getStatistics();   //  prepare the summary of the actually active database

    //      display ''Suggest a new URL' and 'footer'
    if (!$embedded) {
        include "".$template_dir."/html/090_footer.html" ;
    } else {
        include "".$template_dir."/html/091_footer.html" ;    //    no </body> and >/html>
    }
?>