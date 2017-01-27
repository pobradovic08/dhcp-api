<?php
/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/13/2017
 * Time: 1:52 PM
 */

namespace Dhcp\Middleware;

/**
 * Class LogMiddleware
 *
 * @author  Pavle Obradovic <pobradovic08@gmail.com>
 */
class LogMiddleware {

    public function __construct ($ci) {
        $this->ci = $ci;
    }

    /**
     * Log the IP address, HTTP method, URL and parameters that client called.
     * @param $request
     * @param $response
     * @param $next
     * @return mixed
     */
    public function __invoke ($request, $response, $next) {
        $this->ci->logger->addDebug(
            "{$request->getAttribute('ip_address')} called '{$request->getMethod()}' on '{$request->getUri()->getPath()}'",
            $request->getQueryParams()
        );
        $response = $next($request, $response);
        return $response;
    }
}