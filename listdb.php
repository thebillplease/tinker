 <?php
  try
  {
    //open the database
    $db = new PDO('sqlite:postActivity.sqlite');

    //create the database
    $db->exec("CREATE TABLE Uploads (Id INTEGER PRIMARY KEY, modifiedDate INTEGER, fileName TEXT UNIQUE, sent INTEGER)");    
	$directory = dirname(__FILE__) . '/folder/';
    $filenames = array();
    $files = array();
    $iterator = new DirectoryIterator($directory);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isFile()) {
            $filenames[$fileinfo->getMTime()] = $fileinfo->getFilename();
        }
    }
    ksort($filenames);
    foreach($filenames as $x=>$x_value)
            {
            $db->exec("INSERT OR IGNORE INTO Uploads (modifiedDate,fileName, sent) VALUES ('$x', '$x_value', 0);");
            	if ($x['Sent'] == 0){
            		 //add it to the array
            	$files[] = $x_value;
            		}
            }
               
// email fields: to, from, subject, and so on
$to = " ";
$from = " "; 
$subject =" "; 
$message = " ";
$headers = "From: $from";
 
// boundary 
$semi_rand = md5(time()); 
$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
 
// headers for attachment 
$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 
 
// multipart boundary 
$message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n"; 
$message .= "--{$mime_boundary}\n";
 
// preparing attachments
for($x=0;$x<count($files);$x++){
	$file = fopen($files[$x],"rb");
	$data = fread($file,filesize($files[$x]));
	fclose($file);
	$data = chunk_split(base64_encode($data));
	$message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"$files[$x]\"\n" . 
	"Content-Disposition: attachment;\n" . " filename=\"$files[$x]\"\n" . 
	"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
	$message .= "--{$mime_boundary}\n";
}

$ok = @mail($to, $subject, $message, $headers); 
if ($ok) { 
	echo "<p>mail sent to $to!</p>"; 
	$db->exec("UPDATE Uploads (sent) VALUES (1) WHERE sent = $files;");
} else { 
	echo "<p>mail could not be sent!</p>"; 
} 
    // close the database connection
    $db = NULL;
  
  }
  catch(PDOException $e)
  {
    print 'Exception : '.$e->getMessage();
  }
?>