<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use Grav\Common\Flex\Types\Pages\PageObject;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Data;
use Grav\Common\Grav;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;


require_once 'adapters/imagick.php';
require_once 'adapters/gd.php';

/**
 * Class AutomagicImagesPlugin
 * @package Grav\Plugin
 */
class AutomagicImagesPlugin extends Plugin
{
    /**
     * @var string
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $sizes;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onAdminSave'       => ['onAdminSave', 0],
            'onOutputGenerated' => ['onOutputGenerated', 0]
        ];
    }

    /**
     * Determine whether a particular dependency is installed.
     * @param  string $adapter Either 'gd' or 'imagick'
     * @return bool
     */
    protected function dependencyCheck($adapter = 'gd')
    {
        if ($adapter === 'gd') {
            return extension_loaded('gd');
        }

        if ($adapter === 'imagick') {
            return class_exists('\Imagick');
        }
    }

    /**
     * Determine which adapter is preferred and whether or not it's available.
     * Construct an instance of that adapter and return it.
     * @param  string $source - Source image path
     * @return mixed          - Either an instance of ImagickAdapter, GDAdapter or false if none of the extensions were available
     */
    protected function getImageAdapter($source)
    {
        $imagick_exists = $this->dependencyCheck('imagick');
        $gd_exists = $this->dependencyCheck('gd');

        if ($this->adapter === 'imagick') {
            if ($imagick_exists) {
                return new ImagickAdapter($source);
            } else if ($gd_exists) {
                return new GDAdapter($source);
            }
        } else if ($this->adapter === 'gd') {
            if ($gd_exists) {
                return new GDAdapter($source);
            } else if ($imagick_exists) {
                return new ImagickAdapter($source);
            }
        }
    }

    /**
     * Resizes an image using either Imagick or GD
     * @param  string $source    - Source image path
     * @param  string $target    - Target image path
     * @param  float $width      - Target width
     * @param  float $height     - Target height
     * @param  int [$quality=95] - Compression quality for target image
     * @return bool              - Returns true on success, otherwise false
     */
    protected function resizeImage($source, $target, $width, $height, $quality = 95)
    {
        $adapter = $this->getImageAdapter($source);
        $adapter->resize($width, $height);
        $adapter->setQuality($quality);

        return $adapter->save($target);
    }

    /**
     * Called when a page is saved from the admin plugin. Will generate
     * responsive image alternatives for images that don't have any.
     */
    public function onAdminSave($event)
    {
        $page = $event['object'];

        if (!($page instanceof Page || $page instanceof PageObject)) {
            return false;
        }

        if (!$this->dependencyCheck('imagick') && !$this->dependencyCheck('gd')) {
            $this->grav['admin']->setMessage('Neither Imagick nor GD seem to be installed. Automagic Images needs one of them to work.', 'warning');
            return;
        }

        $this->sizes = (array) $this->config->get('plugins.automagic-images.sizes');
        $this->adapter = $this->config->get('plugins.automagic-images.adapter', 'imagick');

        foreach ($page->media()->images() as $filename => $medium) {
            $srcset = $medium->srcset(false);

            if ($srcset != '') {
                continue;
            }

            // We can't rely on the path returned from the image's own path
            // method, since it points to the directory where the image is saved
            // rather than where the original is stored. This means it could
            // point to the global image cache directory.
            $page_path = $page->path();
            $source_path = "$page_path/$filename";
            $info = pathinfo($source_path);
            $count = 0;

             foreach ($this->sizes as $i => $size) {
                if ($size['width'] >= $medium->width) {
                    continue;
                }

                $count++;
                $basename = str_replace(" ", "-", $info['filename']);
                $ext = strtolower($info['extension']);
                $dest_path = "{$info['dirname']}/{$basename}@{$count}x.{$ext}";
                $width = $size['width'];
                $quality = $size['quality'];
                $height = ($width / $medium->width) * $medium->height;

                $this->resizeImage($source_path, $dest_path, $width, $height, $quality, $medium->width, $medium->height);
            }

            $remove_original = $this->config->get('plugins.automagic-images.remove_original');

            if ($count > 0) {
                $original_index = $count + 1;

                if ($remove_original) {
                    unlink($source_path);
                } else {
                    rename($source_path, "{$info['dirname']}/{$basename}@{$original_index}x.{$ext}");
                }

                $fixed_source = str_replace($info['filename'], $basename, $source_path);
                $fixed_source = str_replace($info['extension'], $ext, $fixed_source);
                rename("{$info['dirname']}/{$basename}@1x.{$ext}", $fixed_source);
            }

            $message = "Resized $filename $count times";

            if ($remove_original) {
                $message .= ' (and removed the original image)';
            }

            $this->grav['admin']->setMessage($message, 'info');
        }
    }

    /**
     * Iterates over images in page content that was generated via twig and adds 
     * sizes attribute (not cacheable)
     *
     * @return void
     */
    public function onOutputGenerated()
    {
        if ($this->isAdmin()) {
            return;
        }
        $config = (array) $this->config->get('plugins.automagic-images');
        $page = $this->grav['page'];
        // dump($this->grav->output); exit;
        $config = $this->mergeConfig($page); 
        if ($config['enabled']) {
            include __DIR__ . '/vendor/autoload.php';
            $dom = new Dom;
            $dom->loadStr($this->grav->output,
                (new Options())->setCleanupInput(false)
                    );
            $images = $dom->find('img');
            $arrClasses = [];
            foreach ($config['sizesattr'] as $array) {
                $arrClasses[$array['class']] = $array['directive'];
            }
            foreach ($images as $image) {
                $sizesattr = "";
                $classes = explode(" ", $image->getAttribute('class'));
                foreach ($classes as $class) {
                    if (array_key_exists($class, $arrClasses)) {
                        $sizesattr = $arrClasses[$class];
                    }
                }
                if ($sizesattr == "") {
                    if (array_key_exists('default', $arrClasses)) {
                        $sizesattr = $arrClasses['default'];
                    }
                }
                if ($sizesattr != "") {
                    $image->setAttribute('sizes', $sizesattr);
                }
            }
            $this->grav->output = $dom->outerHtml;
        }
    }
}
