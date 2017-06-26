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

namespace Dhcp\Controller;

use Dhcp\Model\EndHostTypeModel;
use Dhcp\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class EndHostTypeController
 *
 * @author  Pavle Obradovic <pobradovic08@gmail.com>
 */
class EndHostTypeController extends BaseController {

    /**
     * Returns all endhost types
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Not used
     * @return ResponseInterface
     */
    public function get_type (ServerRequestInterface $request, ResponseInterface $response, $args) {
        $types = EndHostTypeModel::all();
        $this->r->success();
        $this->r->setData($types);
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * Returns single endhost type by ID
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should contain 'end_host_type_id' key
     * @return ResponseInterface
     */
    public function get_type_by_id (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validates 'end_host_type_id' route argument
         */
        if (!Validator::validateArgument($args, 'end_host_type_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid host type ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        /*
         * Fetch endhost type or fail with 404 status
         */
        $type = EndHostTypeModel::find($args['end_host_type_id']);
        if ($type) {
            $this->r->success();
            $this->r->setData($type);
        } else {
            $this->r->fail(404);
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * Create new endhost type.
     * Required fields:
     *  - Description
     *
     * @param ServerRequestInterface $request Should contain 'description' parameter
     * @param ResponseInterface $response
     * @param array $args Not used
     * @return ResponseInterface
     */
    public function create_type (ServerRequestInterface $request, ResponseInterface $response, $args) {
        //TODO: refactor code with try/catch and messages
        /*
         * Validate 'description' parameter
         */
        if (!Validator::validateArgument($request->getParams(), 'description', Validator::DESCRIPTION)) {
            $this->r->fail(400, "Required parameter(s) missing or invalid");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        } else {
            /*
             * Create new endhost type with provided parameters
             * and save it to database
             */
            $type = new EndHostTypeModel([
                                             'description' => $request->getParam('description')
                                         ]);
            if ($type->save()) {
                $this->r->success();
                $this->r->setData($type);
            } else {
                $this->r->fail(500);
            }
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
    }

    /**
     * Updates endhost type with ID
     * Editable fields:
     *  - Description
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function update_type (ServerRequestInterface $request, ResponseInterface $response, $args) {
        //TODO: Refactor code with argument array and loop. Add try/catch block
        if (
            !Validator::validateArgument($request->getParams(), 'description', Validator::DESCRIPTION) ||
            !Validator::validateArgument($args, 'end_host_type_id', Validator::ID)
        ) {
            $this->r->fail(400, "Required parameter(s) missing or invalid");
            return $response->withJson($this->r, $this->r->getCode());
        } else {
            /*
             * Create new endhost type and save it to database
             */
            //TODO: no need to create if it doesn't exists. Throw 404 on missing ID
            $type = EndHostTypeModel::firstOrCreate([
                                                        'end_host_type_id' => $args['end_host_type_id']
                                                    ]);
            $type->description = $request->getParam('description');
            if ($type->save()) {
                $this->r->success();
                $this->r->setData($type);
            } else {
                $this->r->fail(500);
            }
            return $response->withJson($this->r, $this->r->getCode());
        }
    }

    /**
     * Delete endhost type by ID
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should contain 'end_host_type_id' key
     * @return ResponseInterface
     */
    public function delete_type (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'end_host_type_id' route argument
         */
        if (!Validator::validateArgument($args, 'end_host_type_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid host type ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        /*
         * Find endhost type by ID and delete it.
         * Fail with 404 if ID doesn't exists
         * Fail with 500 if couldn't delete
         */
        try {
            $type = EndHostTypeModel::findOrFail($args['end_host_type_id']);
            if ($type->delete()) {
                $this->r->success('Host type ' . $type->description . ' deleted');
                $this->ci->logger->addInfo("Deleted end host type #" . $args['end_host_type_id']);
            } else {
                $this->r->fail(505);
            }
        }catch (ModelNotFoundException $e){
            $this->r->fail(404, "Host type #{$args['end_host_type_id']} not found");
            $this->ci->logger->addInfo("Couldn't find end host type #" . $args['end_host_type_id']);
        }
        return $response->withJson($this->r, $this->r->getCode());
    }
}
