# v1.2
##  24-04-2026
1. [](#bugfix)
    * the last version turned out to purge javascript tags, new version of HTML parsing implemented

# v1.1.1
##  01-04-2026
1. [](#bugfix)
    * version number was missing from blueprints.yaml, so no automatic updates


# v1.1
##  18-03-2026

1. [](#bugfix)
    * line breaks are now not removed from output (this interfered with code blocks)
2. [](#improved)
    * removed “resized image 0 times” message


# v1.0.5
##  14-06-2021

1. [](#bugfix)
    * included vendor code…

# v1.0.4
##  11-06-2021

1. [](#bugfix)
    * removed debugging code

# v1.0.3
##  10-06-2021

1. [](#improved)
    * reverted the output changes because they didn't work for twig-generated content

# v1.0.2
##  27-05-2021

1. [](#bugfix)
    * fixed a bit of rogue code from the last update
2. [](#improved)
    * added some more information in the README about Grav’s resizing bugs


# v1.0.1
##  25-05-2021

1. [](#improved)
    * made the output changes that add the sizes attributes cacheable


# v1.0
##  10-05-2021

1. [](#new)
    * Initial release as a fork from Fredrik Ekelund’s plugin Resize Images, which seems unmaintained
2. [](#improved)
    * added translation file and German translation for Admin settings
    * adapted code from Ole Vik’s plugin Image Scrset to allow sizes attributes to be set per CSS class in the plugin settings
