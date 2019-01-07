<?php

/**
 * This file is part of Herbie.
 *
 * (c) Thomas Breuss <https://www.tebe.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Herbie\Url;

use Herbie\Environment;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The URLGenerator creates URLs based on the given route.
 */
class UrlGenerator
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var bool
     */
    private $niceUrls;

    /**
     * Constructor
     * @param ServerRequestInterface $request The request object.
     * @param Environment $environment
     * @param bool $niceUrls Whether to generate nice URLs.
     */
    public function __construct(ServerRequestInterface $request, Environment $environment, bool $niceUrls)
    {
        $this->request = $request;
        $this->environment = $environment;
        $this->niceUrls = $niceUrls;
    }

    /**
     * Generates a relative URL based on the given route.
     * @param string $route The URL route. This should be in the format of 'route/to/a/page'.
     * @return string The generated relative URL.
     */
    public function generate(string $route): string
    {
        $route = ltrim($route, '/');
        if ($this->niceUrls) {
            $url = $this->environment->getBasePath() . '/' . $route;
        } else {
            $url = $this->environment->getScriptName() . '/' . $route;
        }
        return $this->filterUrl($url);
    }

    /**
     * Generates an absolute URL based on the given route.
     * @param string $route The URL route. This should be in the format of 'route/to/a/page'.
     * @return string The generated absolute URL.
     */
    public function generateAbsolute(string $route): string
    {
        $path = $this->generate($route);
        $absUrl = $this->request->getUri()->withPath($path);
        return strval($absUrl);
    }

    /**
     * Filters a generated URL.
     * @param string $url The URL.
     * @return string The filtered URL.
     */
    private function filterUrl(string $url): string
    {
        $url = preg_replace('/\/index$/', '', $url);
        $url = rtrim($url, '/');
        return empty($url) ? '/' : $url;
    }
}
