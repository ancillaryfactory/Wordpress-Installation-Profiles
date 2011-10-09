<?php 
	require('../wp-blog-header.php');
	
//////////////////////////////////////////////////////////////
	
	// redirect to login page if not logged in
	if(!function_exists(is_user_logged_in)){
  	  die('Sorry, You do not have access to this page.');
  }
  if ( !current_user_can('install_plugins') ) {
	$returnPath = get_settings('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']);
	echo "<script type='text/javascript'>";
	echo "window.location.href='$returnPath'";
	echo "</script>";
	exit();
  }
	
///////////////////////////////////////////////////////////////

	$lines = $_POST['pluginNames'];
	$linesArray = explode("\n", $lines);
	
	if ( isset($_POST['importSubmit'] ) ) {
		$newFile = $_FILES['importedFile']['tmp_name'];
		$uploadDir = getcwd() . '/profiles/' . $_FILES['importedFile']['name'];
		$moved = move_uploaded_file($newFile,$uploadDir);
	}
	
	// checks for form submission
	if ( isset($_POST['saveProfile']) || isset($_POST['downloadPlugins']) ) {
		
		// checks for new filename or saves over existing file
		if ( !empty($_POST['profileName']) ) {
			$profileName = $_POST['profileName'] . '.profile';
		} else {
			$profileName = $_POST['profileFilename'];
		}
		
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

<script type="text/javascript" src="jquery.simplemodal.1.4.1.min.js"></script>

<script type="text/javascript">
  $(document).ready(function() {
	$('#profileFilename').val('default.profile');
	
	$('#profileFilename').change(function() {
		var filename = $(this).val();
		var filepath = 'profiles/' + filename;
		
		$.ajax({
			url: filepath,
			cache:false,
			success:function(text) {
				$('#pluginNames').val(text);
			}
		});

		$('#profileToDownload').attr('href','download.php?file=' + filename ).attr('title',filename);
	}); // end .change
		
	$('#importFormWrapper').hide();
	$('#toggleImport').click(function() {
		$('#importFormWrapper').slideToggle();
	});
	
	
	$('#profileForm').submit(function() {
		pluginNames = $('#pluginNames');
		if ( pluginNames.val().length == 0 ) {
			pluginNames.css('border-color','red').focus();
			return false;
		} 
	});
	
	$('#downloadPlugins').click(function() {
		$('#downloadPlugins').val('Downloading...');
		$.modal('<div><p>Downloading from the Wordpress plugin directory<br/><br/><img src="89.gif" /></p></div>');
	});
	
 });
</script>


<style type="text/css">
<!--
body {font-family: Arial, Helvetica, sans-serif;font-size:12px;background:#999}
#downloadSuccessList {background:#A6F29F;margin-bottom: 30px;}
a, a:visited {color:#000}
#pluginNames, #profileName {border:1px solid #999;padding:5px}
.success {background:#F7D065;padding:5px}
h2 {border-bottom: 2px solid;color: #c2c2c2;margin-bottom: 30px;margin-top: 0;padding-bottom: 10px;}

#importForm {padding: 0 10px 4px;text-align:right;background:#d5d5d5;border-radius:10px;width: 412px;}
#importFormWrapper {margin-bottom:40px;overflow:hidden}

#profileFilename {padding:5px;}
.message {border-radius: 10px;padding: 10px;}
#wrapper {margin:50px auto;width:600px;padding:40px;border-radius:10px;background:#fff;position:relative;}

#simplemodal-data {color:#000;padding:40px;background:#d3d3d3;opacity:0.9;font-size:16px;width:500px;font-weight:bold;text-align:center;border-radius:10px}

-->
</style>
</head>

<body>
	<div id="wrapper" >
	
	<h2>WP Installation Profile for <br/><span style="color:#777"><?php echo site_url(); ?></span></h2>
	
	<?php if ( ($written > 0) && !isset($_POST['downloadPlugins']) ) { ?>
		<p class="success message"><strong><?php print $profileName; ?></strong> saved.&nbsp;  
		<a href="download.php?file=<?php print $profileName ?>">Download</a>
		</p>
	<?php } ?>
	
	<?php if ( $moved ) { ?>
		<p class="success message">Imported <?php print $_FILES['importedFile']['name']; ?>. </p>
	<?php } ?>
	
<!-- <pre><?php //  print_r($_POST); 
//print $written . ' to profileName: ' . $profileName;
?></pre> -->
		
		<?php 
		if ( !empty($lines) && $_POST['downloadPlugins'] ) { ?>
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
					
					if ( !empty($pluginURL) ) {
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
					}
					
					if ( $downloadTest > 0 ) {
						$delete = unlink($filename);
						print '<li><a href="' . $apiHomepage . '" target="_blank">'. $apiName . '</a> ' . $apiVersion . '</li>';
					} else {
						print "<li>Couldn't find <strong>'" . $line . "'</strong></li>";
					}  
				
				
			} // end foreach  ?>
			</ul>		
			</div>
		<?php } // end if isset ?>
	
	<p style="margin-top:0px;float:right;text-align: right;width: 160px;"><a href="#" id="toggleImport"><strong>Import profile</strong></a><br/><br/>
	<a id="profileToDownload" title="defautl.profile" href="download.php?file=default.profile"><strong>Download current profile</strong></a>
	
	</p>
	
	<div id="importFormWrapper" >
		<form method="post" action="" enctype="multipart/form-data" id="importForm">
			<p style="margin-top:0"><br/>
			<strong>Upload: </strong>
			<input type="file" name="importedFile" />
			<input type="submit" name="importSubmit" value="Upload" /></p>
		</form>
	</div>
	
	
	<form method="post" action="" id="profileForm">
		<p>
		
		<strong>Choose:</strong><br/>
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
		<br/><br/>
		<strong>Or save this profile as:</strong><br/>
			<input type="text" name="profileName" id="profileName" style="width:200px;" placeholder="Name"/>
		</p><br/>
		
		<p><strong>Plugins</strong> <em>(names found in the <a href="http://wordpress.org/extend/plugins/" target="_blank">Wordpress Plugin Directory</a>)</em>:<br/>
			<textarea name="pluginNames" id="pluginNames" rows="15" cols="46"><?php print $defaultLines; ?></textarea>
		</p>
		<input type="submit" name="saveProfile" value="Save profile" style="padding:5px"/>&nbsp;&nbsp;
		<input type="submit" name="downloadPlugins" value="Download plugins" style="padding:5px" id="downloadPlugins"/>
	</form>
	
	</div> <!-- end wrapper -->
</body>
</html>
