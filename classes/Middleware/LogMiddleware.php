<?php

/**
 * ISC-DHCP Web API
 * Copyright (C) 2016  Pavle Obradovic (pajaja)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
     *
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