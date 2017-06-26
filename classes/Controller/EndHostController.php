<?php

namespace Dhcp\Controller;

use Dhcp\Model\EndHostModel;
use Dhcp\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Expression;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class EndHostController
 *
 * @author  Pavle Obradovic <pobradovic08@gmail.com>
 */
class EndHostController extends BaseController {

    /**
     * Returns all endhosts
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Not used
     * @return ResponseInterface
     */
    public function get_host (ServerRequestInterface $request, ResponseInterface $response, array $args) {
        $hosts = EndHostModel::all();
        $this->r->setData($hosts);
        $this->r->success();
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * Returns a single endhost by ID
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should contain 'end_host_id' key
     * @return ResponseInterface
     */
    public function get_host_by_id (ServerRequestInterface $request, ResponseInterface $response, array $args) {
        /*
         * Validate 'end_host_id' route argument
         */
        if (!Validator::validateArgument($args, 'end_host_id', Validator::ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid host ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        /*
         * If endhost with ID is found return it or fail with 404 status
         */
        try {
            $endhost = EndHostModel::findOrFail($args['end_host_id']);
            $this->r->setData($endhost);
            $this->r->success();
        } catch (ModelNotFoundException $e) {
            $this->r->fail(404, "End host with ID#{$args['end_host_id']} not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * Get a single endhost by MAC address
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should contain 'mac' key
     * @return ResponseInterface
     */
    public function get_host_by_mac (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'mac' route argument
         */
        if (!Validator::validateArgument($args, 'mac', Validator::MAC)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid MAC");
            $this->r->fail(400, "Invalid MAC address");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        /*
         * Remove all irrelevant characters (dot, colon, dash) rom MAC address
         * This leaves MAC address as a valid hex number
         */
        $clean_mac = preg_replace('/[\.:-]/', '', $args['mac']);
        /*
         * Fetch single endhost with MAC address (converted to decimal system)
         * If not found fail with 404 status
         */
        $endhost = EndHostModel::where('mac', '=', intval($clean_mac, 16))->first();
        if ($endhost) {
            $this->r->setData($endhost);
            $this->r->success();
        } else {
            $this->r->fail(404, "End host with MAC {$args['mac']} not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * Get multiple endhosts that match provided search key
     * Elements searched are:
     *  - Description
     *  - Hostname
     *  - MAC address
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should contain 'pattern' key
     * @return ResponseInterface
     */
    public function get_search_host (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Create string representation of MAC address (or part of it)
         * by removing all characters that are not 0-9 or A-F (hexadecimal)
         * This will be used for matching MAC address in the database
         */
        $mac = preg_replace('/[^%0-9A-Fa-f]/i', '', $args['pattern']);
        $endhosts = EndHostModel::where('description', 'like', "%{$args['pattern']}%")
                                ->where('hostname', 'like', "%{$args['pattern']}%", 'or')
                                ->where(new Expression('HEX(mac)'), 'like', "%{$mac}%", 'or')->get();
        if (sizeof($endhosts) >= 1) {
            $this->r->success();
            $this->r->setData($endhosts);
        } else {
            $this->r->fail(404, "No matches found");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * Creates new endhost with unique MAC address and hostname
     * Required parameters are:
     *  - Hostname
     *  - MAC address
     *  - Endhost type ID
     * Optional parameters are:
     *  - Description
     *  - Production state
     *
     * @param ServerRequestInterface $request Should contain 'hostname', 'mac' and 'end_host_type_id'
     * @param ResponseInterface $response
     * @param array $args Not used
     * @return ResponseInterface
     */
    public function post_host (ServerRequestInterface $request, ResponseInterface $response, $args) {
        $required_params = [
            ['hostname', Validator::HOSTNAME],
            ['mac', Validator::MAC],
            ['end_host_type_id', Validator::ID],
        ];
        $optional_params = [
            ['description', Validator::DESCRIPTION],
            ['production', Validator::REGEXP_BOOL],
        ];
        /*
         * Parameters from $request are filtered and copied to this array.
         * It is used for creating new endhost
         */
        $data = [];
        /*
         * Loop trough required parameters and check if
         * they exist and are matching the regexp defined above.
         * Generates error message if the value is missing or doesn't
         * match the validation rule defined for it
         */
        foreach ($required_params as $param) {
            if (Validator::validateArgument($request->getParams(), $param[0], $param[1])) {
                $data[$param[0]] = $request->getParam($param[0]);
            } else {
                $this->r->fail(400, "Required parameter {$param[0]} missing or invalid.");
                return $response->withStatus($this->r->getCode())->withJson($this->r);
            }
        }
        /*
         * No error message is generated if optional parameter is missing.
         * If the value is not matching the regexp, parameter is not
         * added to data array.
         */
        foreach ($optional_params as $param) {
            if (Validator::validateArgument($request->getParams(), $param[0], $param[1])) {
                $data[$param[0]] = $request->getParam($param[0]);
            }
        }
        /*
         * Create new Endhost
         * Requirements:
         * 1. Unique MAC address
         * 2. Unique hostname
         */
        $endhost = new EndHostModel($data);
        $existing = EndHostModel::where('mac', '=', $endhost->mac_decimal)
                                ->where('hostname', '=', $endhost->hostname, 'or')
                                ->first();
        if ($existing) {
            $this->r->fail(400, "Hostname or MAC belong to: {$existing->hostname} [{$existing->mac}]");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        /*
         * New endhost is unique, save it to database or fail with 500 status
         */
        if ($endhost->save()) {
            $this->r->success();
            $this->r->setData($endhost);
            $this->ci->logger->addInfo("Created new end host with ID#" . $endhost->end_host_id);
        } else {
            $this->r->fail(500, "Couldn't create new endhost");
            $this->ci->logger->addError("Failed adding new endhost.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * Update existing endhost with ID
     * Editable parameters are:
     *  - Hostname
     *  - Endhost type
     *  - Description
     *  - Production state
     *
     * @param ServerRequestInterface $request No required parameters
     * @param ResponseInterface $response
     * @param array $args Should contain 'end_host_id' key
     * @return ResponseInterface
     */
    public function update_host (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'end_host_id' route argument
         */
        if (!Validator::validateArgument($args, 'end_host_id', Validator::ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid host ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        /*
         * Fetch endhost with ID or fail with 404 status
         */
        try {
            $endhost = EndHostModel::findOrFail($args['end_host_id']);

            /*
             * Editable fields
             */
            $optional_params = [
                ['hostname', Validator::HOSTNAME],
                ['end_host_type_id', Validator::ID],
                ['description', Validator::DESCRIPTION],
                ['production', Validator::REGEXP_BOOL],
            ];
            /*
             * Loop through optional parameters and check if
             * they exist and are matching the rule defined above.
             * If the validation is successful update endhost parameter.
             * No error message is generated if the parameter is missing.
             */
            foreach ($optional_params as $param) {
                if (Validator::validateArgument($request->getParams(), $param[0], $param[1])) {
                    $endhost->{$param[0]} = $request->getParam($param[0]);
                }
            }
            if ($endhost->save()) {
                $this->r->setData($endhost);
                $this->r->success("Host {$endhost->hostname} updated.");
            } else {
                $this->r->fail("Couldn't update host {$endhost->hostname}.");
            }

        } catch (ModelNotFoundException $e) {
            $this->r->fail(404, "End host with ID#{$args['end_host_id']} not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * Delete endhost by ID
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should contain 'end_host_id' key
     * @return ResponseInterface
     */
    public function delete_host (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'end_host_id' route argument
         */
        if (!Validator::validateArgument($args, 'end_host_id', Validator::ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid host ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        /*
         * If endhost exists delete it or fail with 404 status
         */
        try {
            $endhost = EndHostModel::findOrFail($args['end_host_id']);
            if ($endhost->delete()) {
                $this->r->success("Endhost {$endhost->hostname} deleted.");
            }
        } catch (ModelNotFoundException $e) {
            $this->r->fail(404, "End host with ID#{$args['end_host_id']} not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }
}
