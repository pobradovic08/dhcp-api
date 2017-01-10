<?php

namespace Dhcp\EndHostType;

use Dhcp\EndHostType\EndHostTypeModel;
use Dhcp\Validator;
use \Interop\Container\ContainerInterface as ContainerInterface;

class EndHostTypeController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
        $this->ci->capsule;
    }

    /*
     * Get all types
     * HTTP GET
     */
    public function get_type ($request, $response, $args) {
        // API Response
        $r = new \Dhcp\Response();
        // Log request info
        $this->ci->logger->addInfo("End host type list");
        $types = EndHostTypeModel::all();
        // Prepare API response
        $r->success();
        $r->setData($types);
        return $response->withStatus($r->getCode())->withJson($r);
    }

    /*
     * Get type with specific ID
     * HTTP GET
     */
    public function get_type_by_id ($request, $response, $args) {
        // API Response
        $r = new \Dhcp\Response();
        if (!Validator::validateArgument($args, 'end_host_type_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $r->fail(400, "Invalid host type ID");
            return $response->withStatus($r->getCode())->withJson($r);
        }
        // Log request info
        $this->ci->logger->addInfo("End host type #" . $args['end_host_type_id']);
        $type = EndHostTypeModel::find($args['end_host_type_id']);
        // Prepare API response
        if ($type) {
            $r->success();
            $r->setData($type);
        } else {
            $r->fail(404);
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    /*
     * Create new type
     * HTTP POST
     */
    public function create_type ($request, $response, $args) {
        $r = new \Dhcp\Response();
        $this->ci->logger->addInfo("Creating new end host type with description: \"" . $request->getParam('description') . '"');
        if (!Validator::validateArgument($request->getParams(), 'description', Validator::DESCRIPTION)) {
            $r->fail(400, "Required parameter(s) missing or invalid");
            return $response->withStatus($r->getCode())->withJson($r);
        } else {
            $type = new EndHostTypeModel([
                                             'description' => $request->getParam('description')
                                         ]);
            if ($type->save()) {
                $r->success();
                $r->setData($type);
            } else {
                $r->fail(500);
            }
            return $response->withStatus($r->getCode())->withJson($r);
        }
    }

    /*
     * Update existing type or create a new one
     * HTTP PUT
     */
    public function update_type ($request, $response, $args) {
        $r = new \Dhcp\Response();
        $this->ci->logger->addInfo("Updating End host type entry #" . $args['end_host_type_id']);
        if (
            !Validator::validateArgument($request->getParams(), 'description', Validator::DESCRIPTION) ||
            !Validator::validateArgument($args, 'end_host_type_id', Validator::ID)
        ) {
            $r->fail(400, "Required parameter(s) missing or invalid");
            return $response->withStatus($r->getCode())->withJson($r);
        } else {
            $type = EndHostTypeModel::firstOrCreate([
                                                        'end_host_type_id' => $args['end_host_type_id']
                                                    ]);
            $type->description = $request->getParam('description');
            if ($type->save()) {
                $r->success();
                $r->setData($type);
            } else {
                $r->fail(500);
            }
            return $response->withStatus($r->getCode())->withJson($r);
        }
    }

    /*
     * Delete type with specific ID
     * HTTP DELETE
     */
    public function delete_type ($request, $response, $args){
        // API Response
        $r = new \Dhcp\Response();
        if (!Validator::validateArgument($args, 'end_host_type_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $r->fail(400, "Invalid host type ID");
            return $response->withStatus($r->getCode())->withJson($r);
        }
        // Log request info
        $this->ci->logger->addInfo("Delete end host type #" . $args['end_host_type_id']);
        try {
            $type = EndHostTypeModel::findOrFail($args['end_host_type_id']);
            // Prepare API response
            if ($type->delete()) {
                $r->success('Host type ' . $type->description . ' deleted');
                $this->ci->logger->addInfo("Deleted end host type #" . $args['end_host_type_id']);
            } else {
                $r->fail(404);
            }
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            $r->fail(404, "Host type #{$args['end_host_type_id']} not found");
            $this->ci->logger->addInfo("Couldn't find end host type #" . $args['end_host_type_id']);
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }
}
