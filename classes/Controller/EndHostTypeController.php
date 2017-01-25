<?php

namespace Dhcp\Controller;

use Dhcp\Model\EndHostTypeModel;
use Dhcp\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EndHostTypeController extends BaseController {

    /*
     * Get all types
     * HTTP GET
     */
    public function get_type ($request, $response, $args) {
        $types = EndHostTypeModel::all();
        $this->r->success();
        $this->r->setData($types);
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /*
     * Get type with specific ID
     * HTTP GET
     */
    public function get_type_by_id ($request, $response, $args) {
        // API Response
        if (!Validator::validateArgument($args, 'end_host_type_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid host type ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        $type = EndHostTypeModel::find($args['end_host_type_id']);
        // Prepare API response
        if ($type) {
            $this->r->success();
            $this->r->setData($type);
        } else {
            $this->r->fail(404);
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /*
     * Create new type
     * HTTP POST
     */
    public function create_type ($request, $response, $args) {
        if (!Validator::validateArgument($request->getParams(), 'description', Validator::DESCRIPTION)) {
            $this->r->fail(400, "Required parameter(s) missing or invalid");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        } else {
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

    /*
     * Update existing type or create a new one
     * HTTP PUT
     */
    public function update_type ($request, $response, $args) {
        if (
            !Validator::validateArgument($request->getParams(), 'description', Validator::DESCRIPTION) ||
            !Validator::validateArgument($args, 'end_host_type_id', Validator::ID)
        ) {
            $this->r->fail(400, "Required parameter(s) missing or invalid");
            return $response->withJson($this->r, $this->r->getCode());
        } else {
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

    /*
     * Delete type with specific ID
     * HTTP DELETE
     */
    public function delete_type ($request, $response, $args){
        // API Response
        if (!Validator::validateArgument($args, 'end_host_type_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid host type ID");
            return $response->withJson($this->r, $this->r->getCode());
        }
        try {
            $type = EndHostTypeModel::findOrFail($args['end_host_type_id']);
            // Prepare API response
            if ($type->delete()) {
                $this->r->success('Host type ' . $type->description . ' deleted');
                $this->ci->logger->addInfo("Deleted end host type #" . $args['end_host_type_id']);
            } else {
                $this->r->fail(404);
            }
        }catch (ModelNotFoundException $e){
            $this->r->fail(404, "Host type #{$args['end_host_type_id']} not found");
            $this->ci->logger->addInfo("Couldn't find end host type #" . $args['end_host_type_id']);
        }
        return $response->withJson($this->r, $this->r->getCode());
    }
}
