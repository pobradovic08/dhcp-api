<?php

namespace Dhcp\Controller;

/**
 * Created by PhpStorm.
 * User: pajaja
 * Date: 1/3/2017
 * Time: 11:20 PM
 */

//Custom
use Dhcp\Model\GroupModel;
use Dhcp\Validator;
//Eloquent
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class GroupController extends BaseController {


    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function get_groups (ServerRequestInterface $request, ResponseInterface $response, $args) {
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        $subnet_id = intval($args['subnet_id']);
        $groups = GroupModel::where('subnet_id', '=', $subnet_id)->without('subnets')->get();
        if (!$groups->isEmpty()) {
            $this->r->success();
            $this->r->setData($groups);
        } else {
            $this->r->fail(404, "Subnet #$subnet_id not found.");
        }
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function get_group_by_id (ServerRequestInterface $request, ResponseInterface $response, $args) {
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        if (!Validator::validateArgument($args, 'group_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid group ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        try {
            $group = GroupModel::findOrFail($args['group_id']);
            if ($group->subnet_id == $args['subnet_id']) {
                $this->r->success();
                $this->r->setData($group);
            } else {
                $this->r->fail(404, "Group with ID#{$args['group_id']} doesn't belong to subnet #{$args['subnet_id']}");
            }
        } catch (ModelNotFoundException $e) {
            $this->r->fail(404, 'No group with ID #' . $args['group_id']);
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function post_group (ServerRequestInterface $request, ResponseInterface $response, $args) {
        $required_params = [
            ['subnet_id', Validator::ID],
            ['name', Validator::FILENAME],
        ];
        $optional_params = [
            ['description', Validator::DESCRIPTION],
        ];
        /*
         * Data array used for building the Group object.
         * Parameters from Request are filtered and copied to this array.
         */
        $data = [];
        /*
         * Loop trough required parameters and check if
         * they exist and are matching the regexp defined above.
         * Generates error message if the value is missing or doesn't
         * match the regular expression defined for it
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
         * Loop through optional parameters and check if
         * they exist and are matching the regexp defined above.
         * No error message is generated if the parameter is missing.
         * If the value is not matching the regexp, parameter is not
         * added to data array.
         */
        foreach ($optional_params as $param) {
            if (Validator::validateArgument($request->getParams(), $param[0], $param[1])) {
                $data[$param[0]] = $request->getParam($param[0]);
            }
        }

        $existing = GroupModel::where('name', '=', $data['name'])->first();
        if ($existing) {
            $this->r->fail(400, "Group already exists");
            $this->r->setData($existing);
        } else {
            $group = new GroupModel($data);
            if ($group->save()) {
                $this->r->success("Created group #{$group->group_id}");
                $this->r->setData($group);
            } else {
                $this->r->fail(500, "Failed creating new group");
            }
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function put_group (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Check if subnet ID or group ID are valid
         */
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        if (!Validator::validateArgument($args, 'group_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid group ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }

        $optional_params = [
            ['subnet_id', Validator::ID],
            ['name', Validator::FILENAME],
            ['description', Validator::DESCRIPTION],
        ];
        /*
         * Data array used for building the Group object.
         * Parameters from Request are filtered and copied to this array.
         */
        $data = [];

        /*
         * Loop through optional parameters and check if
         * they exist and are matching the regexp defined above.
         * No error message is generated if the parameter is missing.
         * If the value is not matching the regexp, parameter is not
         * added to data array.
         */
        foreach ($optional_params as $param) {
            if (Validator::validateArgument($request->getParams(), $param[0], $param[1])) {
                $data[$param[0]] = $request->getParam($param[0]);
            }
        }

        if (empty($data)) {
            $this->r->fail(400, "No arguments passed. Nothing to update");
        } else {
            try {
                $existing = GroupModel::findOrFail($args['group_id']);
                foreach ($data as $field => $value) {
                    $existing->$field = $value;
                }
                if ($existing->save()) {
                    $this->r->success("Group updated");
                    $this->r->setData($existing);
                } else {
                    $this->r->fail(500, "Failed to update group entry.");
                }

            } catch (ModelNotFoundException $e) {
                $this->r->fail(404, "Group with ID {$args['group_id']} not found.");
            } catch (QueryException $e) {
                $this->r->fail(500, "Error updating group.");
                $this->ci->logger->addError("Failed updating group: " . $e->getMessage());
            }
        }
        return $response->withJson($this->r, $this->r->getCode());
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function delete_group (ServerRequestInterface $request, ResponseInterface $response, $args) {
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        if (!Validator::validateArgument($args, 'group_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid group ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        try {
            $group = GroupModel::findOrFail($args['group_id']);
            //TODO: clean this.
            if ($group->subnet_id == $args['subnet_id']) {
                if ($group->delete()) {
                    $this->r->success("Group #{$group->group_id} deleted.");
                } else {
                    $this->r->fail(500, "Couldn't delete group #{$group->group_id}");
                }
            } else {
                $this->r->fail(404, "Group with ID#{$args['group_id']} doesn't belong to subnet #{$args['subnet_id']}");
            }
        } catch (ModelNotFoundException $e) {
            $this->r->fail(404, 'No group with ID #' . $args['group_id']);
        }
        return $response->withJson($this->r, $this->r->getCode());
    }
}