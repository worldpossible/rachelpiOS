<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Upload Local</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>
<a href="../index.html" id="rachel">Rachel</a>
<div id="content">

<?php
// Where the file is going to be placed 
$target_path = "../local/";

/* Add the original filename to our target path.  
Result is "uploads/filename.extension" */
$target_path = $target_path . basename( $_FILES['uploadedfile']['name']);  

if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
    echo "The file ".  basename( $_FILES['uploadedfile']['name']). 
    " has been uploaded.";
} else{
    echo "There was an error uploading the file, please try again!";
}
?>
<a href="../local">view upload</a>
</div>
</body>
</html>
