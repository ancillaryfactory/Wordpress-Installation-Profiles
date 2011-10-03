<?php 
	require('../wp-blog-header.php');
	
	$lines = $_POST['pluginNames'];
	$linesArray = explode("\n", $lines);
	
	if ( isset($_POST['profileName']) ) { 
		$profileName = $_POST['profileName'] . '.profile.php';
		$profileName = str_replace(' ', '-', $profileName);
		
		$newProfile = fopen($profileName,"w"); 
		$written =  fwrite($newProfile, $lines);

		fclose($newProfile);
	}
		
		// read data from default profile
		$readDefaults = fopen('default.profile.php',"r");
		$defaultLines = fread($readDefaults, filesize('default.profile.php'));
		fclose($readDefaults);
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="utf-8" />
 <title>WP Installation Profile</title>
 
 <!-- <link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.3.0/bootstrap.min.css"> 
 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js"></script>

<script type="text/javascript">
  $(document).ready(function() {
  
  });
</script>
-->

<style type="text/css">
<!--

body {font-family: Arial, Helvetica, sans-serif;font-size:12px;}
#downloadSuccessList {float:right;}

-->
</style>
</head>

<body>
	<div id="wrapper" style="margin:0 auto;width:600px">
	
	<!-- <pre><?php // print_r($_POST); ?></pre> -->
		
		<?php 
		if (isset($lines)) { ?>
			<div id="downloadSuccessList">
			<p>Downloaded plugins:</p>
			<ul>
			<?php 
			foreach ($linesArray as $line) {
				$apiFilename = str_replace(' ', '-', $line);
				$apiURL = 'http://api.wordpress.org/plugins/info/1.0/' . $apiFilename . '.xml';
				
				
				$plugin = simplexml_load_file($apiURL);

				
				// gets filename from Wordpress API
					$pluginURL = $plugin->download_link;
					$path_parts = pathinfo($pluginURL);
					$filename = $path_parts['filename'] . '.' . $path_parts['extension'];
					$path = $filename;
					
					$ch = curl_init($pluginURL);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				 
					$data = curl_exec($ch);
				 
					curl_close($ch);
				 
					$downloadTest = file_put_contents($path, $data);


				// extracts and deletes zip file
					$zip = new ZipArchive;
						
					if ($zip->open($filename) === TRUE) {
						$zip->extractTo(WP_PLUGIN_DIR);
						$zip->close();
						//echo 'ok';
					} else {
						//echo 'failed';
					}
					
					if ( $downloadTest > 0 ) {
						$delete = unlink($filename);
						print '<li>'. $line .'</li>';
					} else {
						print 'Not downloaded';
					} 
				
				
			} // end foreach  ?>
			</ul>		
			</div>
		<?php } // end if isset ?>
	
	
	<!-- <form>
		<select id="profileFilename" name="profileFilename">
			<?php $profilesList = scandir(getcwd());
			
			foreach ( $profilesList as $profileFile ) {
				if ( preg_match( '(\.php$)', $profileFile) ) {
					echo '<option value="' . $profileFile . '">' . $profileFile . '</option>';
				}
			}			
		?>
		</select>
	</form> -->
	
	<?php
		$profilesList = scandir(getcwd());
		
	
	?>
	<form method="post" action="">
		<p>Profile name (optional): </p>
		<input type="text" name="profileName" /><br/>
		<p>Enter plugin names to download:</p>
		
		<textarea name="pluginNames" rows="15" cols="40"><?php print $defaultLines; ?></textarea><br/>
		<input type="submit" name="submit" value="Download plugins"/>
	</form>
	</div> <!-- end wrapper -->
</body>
</html>
