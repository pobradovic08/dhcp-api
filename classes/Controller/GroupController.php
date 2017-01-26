<?php

namespace Dhcp\Controller;

use Dhcp\Model\GroupModel;
use Dhcp\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GroupController
 *
 * @author  Pavle Obradovic <pobradovic08@gmail.com>
 */
class GroupController extends BaseController {

    /**
     * Get all groups for subnet with ID
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should contain 'subnet_id' key
     * @return ResponseInterface
     */
    public function get_groups (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'subnet_id' route argument
         */
        if (!Validator::validateArgument($args, 'subnet_id', Validator::REGEXP_ID)) {
            $this->ci->logger->addError("Called " . __FUNCTION__ . "with invalid ID");
            $this->r->fail(400, "Invalid subnet ID");
            return $response->withStatus($this->r->getCode())->withJson($this->r);
        }
        /*
         * Get groups and return them, or fail with 404 status
         */
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
     * Get single group by ID
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Should contain 'subnet_id' and 'group_id' keys
     * @return ResponseInterface
     */
    public function get_group_by_id (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'subnet_id' and 'group_id' route arguments
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
        /*
         * Return single group with given ID or fail with 404 status
         */
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
     * Create new group with unique name
     * Required parameters:
     *  - Subnet ID
     *  - Name
     * Optional parameters:
     *  - Description
     *
     * @param ServerRequestInterface $request Should contain 'subnet_id' and 'name' parameters
     * @param ResponseInterface $response
     * @param array $args Not used
     * @return ResponseInterface Returns new Group entry
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
         * Parameters from Request are filtered and copied to this array.
         */
        $data = [];
        /*
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
         * No error message is generated if the parameter is missing.
         * If the value is not matching the regexp, parameter is not
         * added to data array.
         */
        foreach ($optional_params as $param) {
            if (Validator::validateArgument($request->getParams(), $param[0], $param[1])) {
                $data[$param[0]] = $request->getParam($param[0]);
            }
        }

        /*
         * Get group with that name if it exists and fail with 400 status
         * If group doesn't exists, create it and save to database
         */
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
     * Update group entry with specific ID
     * Editable fields:
     *  - Subnet ID
     *  - Name
     *  - Description
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args Should contain 'subnet_id' and 'group_id' keys
     * @return ResponseInterface Returns updated Group entry
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
            /*
             * Update entry if exists or fail with 404 status
             */
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
     * Delete group by ID
     *
     * @param ServerRequestInterface $request Not used
     * @param ResponseInterface $response
     * @param array $args Shuld contain 'subnet_id' and 'group_id' keys
     * @return ResponseInterface
     */
    public function delete_group (ServerRequestInterface $request, ResponseInterface $response, $args) {
        /*
         * Validate 'subnet_id' and 'group_id' route arguments
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
        /*
         * Fetch single group and delete it
         * Fail with 404 if ID doesn't exists
         * Fail with 500 if couldn't delete entry
         */
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