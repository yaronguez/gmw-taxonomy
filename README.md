# Geo My WordPress - Taxonomy Locator Addon

## Overview
This is an addon for the [Geo My WordPress plugin](https://wordpress.org/plugins/geo-my-wp/) that adds geo location support for taxonomies. Using this plugin you can add geo location data to taxonomies and then search for taxonomies by address.  You can also filter taxonomy search results by post ID so a user can search for taxonomies attached to a specific post.  The plugin also allows you to search for posts by location using the geo location data of the post's taxonomy terms.  This plugin is under active development.

## Usage
Visit the Taxonomies tab under the GEO my WP Settings admin page to set defaults and specify which taxonomies should get an address field.

Create a Taxonomy Locator form under the GEO my WP Forms admin page and specify which taxonomies to search and other search settings that are similar to the Posts Locator form.  Embed the form as you would normally using the `[gmw form="<id>"]`.  You can optionally filter search results by post with the `filter_post_id` such as `[gmw form="<form_id>" filter_post_id="<post_id>"]`.  Users can now search for taxonomy terms by address.

Create a Posts by Taxonomy Locator form under the GEO my WP Forms admin page and specify which taxonomies to fetch the address data from.  You can also specify labels for displaying the distance to the closest taxonomy term as well as number of taxonomy terms for each post returned.  Embed the form as you would normally.  Users can now search for posts by address using the address of the attached taxonomy terms.  

**NOTE:** for performance reasons, when using the Posts by Taxonomy Locator form, the details about the closest taxonomy term, such as name, address, etc, are NOT shown displayed.  No map is displayed either. Only the distance to the closest taxonomy term and the number of taxonomy terms matching the search are displayed.  I may add this feature in the future as an optional setting.

## Updates
This plugin ~uses~ will use (can't get it working just yet!) the [GitHub Updater](https://github.com/afragen/github-updater) updater for updates.  If you'd like to keep the plugin updated, [download and install the latest tagged release](https://github.com/afragen/github-updater/releases).

## Support
Extremely limited. I'm [involved](https://www.crypteron.com) [in](http://www.meetup.com/Advanced-WordPress/) [a](http://www.trestian.com) [lot](https://www.facebook.com/achordingtousimprov) [of](http://rotby.com) [projects](http://royalheartmusic.com)!  Pull requests welcome :-)

## Disclaimer
Geo My WordPress was created by Eyal Fitoussi.  This GMW Taxonomy plugin merely extends that one as an addon.  Eyal was very helpful in this plugin's development.
