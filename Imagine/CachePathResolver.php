<?php

namespace Avalanche\Bundle\ImagineBundle\Imagine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CachePathResolver
{
    /**
     * @var string
     */
    private $webRoot;

    /**
     * @var string
     */
    private $sourceRoot;

    /**
     * @var Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * Constructs cache path resolver with a given web root and cache prefix
     *
     * @param string                                    $webRoot
     * @param string                                    $sourceRoot
     * @param Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct($webRoot, $sourceRoot, RouterInterface $router)
    {
        $this->webRoot = $webRoot;
        $this->sourceRoot = $sourceRoot;
        $this->router  = $router;
    }

    /**
     * Gets filtered path for rendering in the browser
     *
     * @param string $path
     * @param string $filter
     * @param boolean $absolute
     */
    public function getBrowserPath($path, $filter, $absolute = UrlGeneratorInterface::RELATIVE_PATH)
    {
        // identify if current path is not under specified source root and return
        // unmodified path in that case
        $realPath = realpath($this->sourceRoot.$path);

        if (!0 === strpos($realPath, $this->sourceRoot)) {
            return $path;
        }

        switch ($absolute) {
            case true:
                $absolute = UrlGeneratorInterface::ABSOLUTE_URL;
                break;
            case false:
                $absolute = UrlGeneratorInterface::RELATIVE_PATH;
                break;
        }

        $path = str_replace(
            urlencode(ltrim($path, '/')),
            urldecode(ltrim($path, '/')),
            $this->router->generate('_imagine_'.$filter, array(
                'path' => ltrim($path, '/')
            ), $absolute)
        );

        $cached = realpath($this->webRoot.$path);

        if (file_exists($cached) && !is_dir($cached) && filemtime($realPath) > filemtime($cached)) {
            unlink($cached);
        }

        return $path;
    }
}
