# Craft Fetch plugin for Craft CMS 3.x

A plugin that let you fetch from external APIs, and handles caching.

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:
	Since this is a private package hosted on github you need to tell composer where to find this package. Add the following code to your `composer.json`: 
	```
	"repositories": [
		{
	  		"type": "vcs",
	  		"url": "git@github.com:anewtypeofinterference/craft-fetch.git"
		}
	]
	```
	Note, repositories might already exisit in your project, then you just have to add the object to the exisitng property.

	Then you run `composer require anti/craft-fetch` as usual. You might get asked to authenticate yourself, if so, follow instructions from composer.

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Craft Fetch.


## Configuring Craft Fetch


## Using Craft Fetch
# craft-fetch
