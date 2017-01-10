<?php 
    // ob_start();
    echo "Hello,PHP!";
    echo '<br/>';
    var_dump(ob_get_contents());
    
    file_put_contents('./make/test1.html', 'I just want to test this file');
?>
