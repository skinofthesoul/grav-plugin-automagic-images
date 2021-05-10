# [Grav](http://getgrav.org) Automagic Images Plugin

This plugin started out as a fork from the most excellent [Resize Images](https://github.com/fredrikekelund/grav-plugin-resize-images) by Fredrik Ekelund. That seems unmaintained however, and I needed some image functionality in my projects that I felt would be best bundled up in one plugin.

## Currently it does two things:

1. It resizes a page's images when you save that page in Admin, according to sizes that can be customised in the plugin's settings.
2. It adds a `sizes` directive to your images, according to their CSS class.

## Why not use Grav's built-in functionality?
In theory, Grav has all the image-handling capabilities I personally need ([see the docs on page media](https://learn.getgrav.org/16/content/media)). However, there are three main reasons why I use this plugin:

1. If you have the Imagick module available, this plugin can use it, resulting in better image quality.
2. There are [currently some bugs](https://github.com/getgrav/grav/issues/3146) in the native image handling system that make it inadvisable to mix cropZoom and the like with setting `sizes` (both in Twig and Markdown).
3. When doing projects for clients, I like to keep things as simple as possible for them, which ideally is "upload an image and Grav does the rest". If an image is used in Markdown (I try to avoid that as well, but it's not always possible), I don't want people to have to deal with setting sizes.

So in short, if you want to lovingly handcraft every image on your site, you do not need this plugin. If you want to set up a system where in the end, you upload an image to a page, click save, and then it just works and the result is fairly good, this is the plugin for you.

A note: Images that already have responsive alternatives with the "@2x" naming convention won't be resized.

## Configuration

You can customize the set of widths that your images will be resized to. By default they are 640, 1000, 1500, 2500, 3500 pixels in width. Images will never be scaled up, however, so only the widths that are smaller than the original image's will be used.

For every width, you're also able to set the JPEG compression quality.  A good rule of thumb is to lower that number at higher widths - the result will still be good!

This plugin won't convert PNGs to JPEGs, so the quality number only applies to JPEG images.

To generate variations of existing images go into the admin panel and re-save the pages where those images live. Every time a page is saved (whether it's new or old), this plugin will go through all images (again, whether they are new or old) in that page, check if they have responsive variants and generate new ones if necessary.

## Installation

Installing the Automagic Images plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install automagic-images

This will install the Automagic Images plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/automagic-images`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `automagic-images`. You can find these files on [GitHub](https://github.com/skinofthesoul/grav-plugin-automagic-images) or via [GetGrav.org](http://getgrav.org/downloads/plugins).

You should now have all the plugin files under

    /your/site/grav/user/plugins/automagic-images

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Many thanks go to
@fredrikekelund – all credit for the actual code that does all the resizing goes to him.
@olevik – copying and adapting his code from Image Srcset to add the `sizes` per CSS class was a lot easier than writing it all by myself, and also I wouldn't have learned about the excellent [PHP Html Parser](https://github.com/paquettg/php-html-parser) otherwise.

## Plans for the future
Being able to regenerate ALL images with a click would be incredibly nice, but I really don't know when I might get around to that. Co-maintainers and -developers are welcome!