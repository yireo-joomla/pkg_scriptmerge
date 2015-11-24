ScriptMerge (pkg_scriptmerge)
===============

The ScriptMerge plugin is a Joomla! System Plugin that merges all the CSS stylesheets and JavaScript files on your Joomla! page into one single file, which means that the browser only needs to download one single file - optimizing the bandwidth needed for your site.

### Features
- Either use dynamic CSS / JS merging or merge to a cache file
- Compress using the compression technique that works best for your specific files
- Exclude any problematic file easily
- Support for advanced features like data URIs and WebP

### Getting started
 - [Download ScriptMerge](https://www.yireo.com/software/joomla-extensions/scriptmerge/downloads)
 - Install the plugin in the Extension Manager
 - Enable the plugin in the Plugin Manager

### Benefits
- Your Joomla pages will fly
- Easy to use, while offering advanced options
- Optimize loading times with a few clicks


### User Reviews
> I love this extension. And your support.
>
> -- <cite>Jordan Weinstein</cite>

### Backgrounds
When optimizing a Joomla! site, many things should be dealt with: Caching should be enabled, PNG-images should be indexed, unneeded Joomla! plugins need to be disabled. But another important trick to speed up your site is to limit the number of HTTP requests made by the browser to load the Joomla! webpage.

The Yireo ScriptMerge plugin performs this trick by altering the Joomla! body-content just before it's sent to the browser. Existing HTML-tags pointing to CSS-stylesheets and JavaScript-files are interpreted, the corresponding file is read and cached and the original HTML-tag is removed from the Joomla! body.

There are various other plugins available doing the same job, but in our tests these plugins did not fullfill the job: They were either written using an older coding standard (without a JPlugin-class) or not checking things properly (for instance whether CSS-files were actually readable). Our ScriptMerge plugin does the job, but just a bit better.

### Warning on JavaScript errors and CSS validation
A big warning needs to be given on using ScriptMerge on your site. Do not use randomly, but first make sure your site is working properly. If file A contains an error and file B does not, merging file A and B together in file C will cause file C to have an error as well. In most cases of JavaScript errors or major CSS issues, ScriptMerge will make things worse - not because ScriptMerge being malfunctioning, but because your site has issues and ScriptMerge will simply magnify these issues.

To check for this, open up your browsers Error Console and navigate your site. If any JavaScript error occurs, make sure to fix it first, before trying to optimize your site further. The same counts for CSS validation. If one CSS file is really corrupt, you can't merge it with other files. For checking this, we recommend the W3C CSS Validator, but you can expect a lot of harmless errors though.

### Credits & Contributions
This plugin has received great improvements from various contributors for which we are very thankful: Jeroen Jansen, Jisse Reitsma, Hans Kuijpers, Babs Gosgens, Ruud van Zuidam.

### USAGE
Check the usage guide for more information.
