<?php
    //      display 'Search form'
    if (!$cat_sel) {
    include "".$template_dir."/html/020_search-form.html" ; //  complete search form
    //include "".$template_dir."/html/021_search-form.html" ; //  like 020 but without 'Categories', 'Mark selection' and without 'Media search'
    //include "".$template_dir."/html/022_search-form.html" ; //  like 021, but also without 'Results per page'
    } else {
        include "".$template_dir."/html/025_search-form.html" ; //  search form for multiple category selection
    }
?>