Wordpress Installation Profiles
v0.3, 10/6/11
jon@ancillaryfactory.com


Download multiple plugins automaticaly from the Wordpress plugin directory. 
	
Installation:
Unzip and copy /wpip to the root folder of your Wordpress installation, and visit http://example.com/wpip


Finding plugin names:
Visit a plugin's listing in the Wordpress plugin directory, and use the plugin slug from the URL, e.g.:
http://wordpress.org/extend/plugins/{plugin-name}


//////////////////////////////////////////////////////////////////


Here's what happens after the 'Download Plugins' form is submitted:
	- WPIP uses the WP plugin directory API to get the URL of the latest version of the plugin
	- Plugin is downloaded to the WPIP folder
	- Plugin file is unzipped to wp-content/plugins folder
	- .zip file is deleted

Saved installation profiles must have filenames ending in '.profile' and be saved in plain text, with one plugin name per line. 