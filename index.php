<?php 
	require('../wp-blog-header.php');
	
	$lines = $_POST['pluginNames'];
	$linesArray = explode("\n", $lines);
	
	if ( isset($_POST['importSubmit'] ) ) {
		$newFile = $_FILES['importedFile']['tmp_name'];
		$uploadDir = getcwd() . '/profiles/' . $_FILES['importedFile']['name'];
		$moved = move_uploaded_file($newFile,$uploadDir);
	}
	
	
	
	if ( isset($_POST['profileName']) && isset($_POST['saveProfile']) ) { 
		$profileName = $_POST['profileName'] . '.profile';
		$profileName = str_replace(' ', '-', $profileName);
		
		$newProfile = fopen('profiles/' . $profileName,"w"); 
		$written =  fwrite($newProfile, $lines);

		fclose($newProfile);
	}
		
		// read data from default profile
		$readDefaults = fopen('profiles/default.profile',"r");
		$defaultLines = fread($readDefaults, filesize('profiles/default.profile'));
		fclose($readDefaults);
?>
<!DOCTYPE html>

<!-- 
Version 0.3


-->

<html lang="en">
    <head>
        <meta charset="utf-8" />
 <title>WP Installation Profile</title>
 
 <!-- <link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.3.0/bootstrap.min.css"> -->
 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>

<script type="text/javascript">
  $(document).ready(function() {
	$('#profileFilename').val('default.profile');
	
	$('#profileFilename').change(function() {
		var filename = 'profiles/' + $(this).val();
		$.get(filename, function(text) {
				$('#pluginNames').val(text);
			});
		});
		
		$('#importFormWrapper').hide();
		$('#toggleImport').click(function() {
			$('#importFormWrapper').slideToggle();
		});
	});
</script>


<style type="text/css">
<!--
body {font-family: Arial, Helvetica, sans-serif;font-size:12px;background:#999}
#downloadSuccessList {background:#A6F29F;}
a, a:visited {color:#000}
#pluginNames, #profileName {border:1px solid #999;padding:5px}
.success {background:#F7D065;padding:5px}
h2 {border-bottom: 2px solid;color: #c2c2c2;margin-bottom: 30px;margin-top: 0;padding-bottom: 10px;}
#importForm {padding: 0 10px 4px;text-align:right;background:#d5d5d5;}
#profileFilename {padding:5px;}
.message {border-radius: 10px;padding: 10px;}

-->
</style>
</head>

<body>
	<div id="wrapper" style="margin:50px auto;width:600px;padding:40px;border-radius:10px;background:#fff">
	
	<h2>WP Installation Profile for <?php echo site_url(); ?></h2>
	
	<?php if ( $written > 0 ) { ?>
		<p class="success message">Saved as <?php print $profileName; ?>. 
		<a href="download.php?file=<?php print $profileName ?>">Download</a>
		</p>
	<?php } ?>
	
	<?php if ( $moved ) { ?>
		<p class="success message">Imported <?php print $_FILES['importedFile']['name']; ?>. </p>
	<?php } ?>
	
<!--<pre><?php // print_r($_FILES['importedFile']); 
// print $uploadDir;
?></pre>-->
		
		<?php 
		if ( isset($lines) && $_POST['downloadPlugins'] ) { ?>
			<div id="downloadSuccessList" class="message">
			<p><strong>Downloaded plugins:</strong> 
				<?php print '<a style="float:right" href="' . admin_url('plugins.php') . '">Visit plugins page</a>'; ?>
			</p>
			<ul>
			<?php 
			foreach ($linesArray as $line) {
				$apiFilename = str_replace(' ', '-', $line);
				$apiURL = 'http://api.wordpress.org/plugins/info/1.0/' . $apiFilename . '.xml';
				
				
				$plugin = simplexml_load_file($apiURL);

				
				// gets filename from Wordpress API
					$pluginURL = $plugin->download_link;
					$apiName = $plugin->name;
					$apiVersion = $plugin->version;
					$apiHomepage = $plugin->homepage;
					
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
						print '<li><a href="' . $apiHomepage . '" target="_blank">'. $apiName . '</a> ' . $apiVersion . '</li>';
					} else {
						print '<li>' . $line . ' not downloaded</li>';
					}  
				
				
			} // end foreach  ?>
			</ul>		
			</div>
		<?php } // end if isset ?>
	
	<p style="margin-top:0px;float:right;text-align: right;width: 90px;"><a href="#" id="toggleImport"><strong>Import profile</strong></a></p>
	
	<div id="importFormWrapper" style="margin-bottom:40px;overflow:hidden">
		<form method="post" action="" enctype="multipart/form-data" id="importForm">
			<p style="margin-top:0">
			<!-- <strong>Import profile:</strong>--><br/>
			<input type="file" name="importedFile" />
			<input type="submit" name="importSubmit" value="Upload" /></p>
		</form>
	</div>
	
	
	<form method="post" action="">
		<p>
		
		<strong>Choose:</strong>
		<select id="profileFilename" name="profileFilename">
			<?php 
			$dir = getcwd() . '/profiles';
			$profilesList = scandir($dir);
			foreach ( $profilesList as $profileFile ) {
				if ( preg_match( '(profile$)', $profileFile) ) {
					$nameLength = stripos($profileFile, '.');
					$name = substr($profileFile,0,$nameLength);
					echo '<option value="' . $profileFile . '">' . $name . '</option>';
				}
			}			
		?>
		</select>
		<strong>&nbsp;&nbsp;or 
		
		save this profile as:&nbsp;&nbsp;</strong>
			<input type="text" name="profileName" id="profileName" style="width:150px;" placeholder="Name"/>
		</p><br/>
		
		<p><strong>Plugins</strong> <em>(names found in the <a href="http://wordpress.org/extend/plugins/" target="_blank">Wordpress Plugin Directory</a>)</em>:<br/>
			<textarea name="pluginNames" id="pluginNames" rows="15" cols="46"><?php print $defaultLines; ?></textarea>
		</p>
		<input type="submit" name="saveProfile" value="Save this profile" style="padding:5px"/>&nbsp;&nbsp;
		<input type="submit" name="downloadPlugins" value="Download plugins" style="padding:5px"/>
	</form>
	
	</div> <!-- end wrapper -->
</body>
</html>
