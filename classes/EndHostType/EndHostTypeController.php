<?php

namespace Dhcp\EndHostType;

use Dhcp\Validator;
use \Interop\Container\ContainerInterface as ContainerInterface;

class EndHostTypeController {
    protected $ci;

    //Constructor
    public function __construct (ContainerInterface $ci) {
        $this->ci = $ci;
    }

    //TODO: Validate arguments
    public function get_type ($request, $response, $args) {
        // API Response
        $r = new \Dhcp\Response();
        // Log request info
        $this->ci->logger->addInfo("End host type list");
        // Instance mapper and get all end host types (empty filter)
        $mapper = new EndHostTypeMapper($this->ci->db);
        $endhost_types = $mapper->getTypes(array ());
        // Build array of end hosts
        $array = [];
        foreach ( $endhost_types as $type ) {
            $array[] = $type->serialize();
        }
        // Prepare API response
        $r->success();
        $r->setData($array);
        return $response->withStatus($r->getCode())->withJson($r);
    }

    //TODO: Validate arguments
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
        // Instance mapper and get end host type with specific ID
        $mapper = new EndHostTypeMapper($this->ci->db);
        $filter = array ('end_host_type_id' => $args['end_host_type_id']);
        $types = $mapper->getTypes($filter);
        // If there's one type all is good
        // Prepare API response
        if (sizeof($types) == 1) {
            $r->success();
            $r->setData($types[0]->serialize());
        } else {
            $r->fail(404);
        }
        return $response->withStatus($r->getCode())->withJson($r);
    }

    public function put_type_by_id ($request, $response, $args) {
        $this->ci->logger->addInfo("Updating End host type entry #" . $args['end_host_type_id']);
        if (!$request->getParam('description') || !$args['end_host_type_id']) {
            return $response->withStatus(400)->withJson(array ('error' => "Required parameters missing"));
        } else {
            $mapper = new EndHostTypeMapper($this->ci->db);
            $data = array (
                'end_host_type_id' => $args['end_host_type_id'],
                'description' => $request->getParam('description')
            );
            $result = $mapper->editType($data);
            if ($result['success']) {
                return $response->withStatus(200)->withJson($result['object']);
            } else {
                return $response->withStatus(400)->withJson($result['object']);
            }
        }
    }

    public function post_type ($request, $response, $args) {
        $this->ci->logger->addInfo("Creating new end host type with description: \"" . $request->getParam('description') . '"');
        $mapper = new EndHostTypeMapper($this->ci->db);
        if (!$request->getParam('description')) {
            return $response->withStatus(400)->withJson(array ('error' => "Required parameter 'description' missing"));
        } else {
            $result = $mapper->addType(filter_var($request->getParam('description', FILTER_SANITIZE_STRING)));
            if ($result['success']) {
                return $response->withStatus(200)->withJson($result['object']);
            } else {
                return $response->withStatus(400)->withJson($result['object']);
            }
        }
    }
}
