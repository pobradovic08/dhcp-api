<?php

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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function get_type (ServerRequestInterface $request, ResponseInterface $response, $args) {
        $types = EndHostTypeModel::all();
        $this->r->success();
        $this->r->setData($types);
        return $response->withStatus($this->r->getCode())->withJson($this->r);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function get_type_by_id (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function create_type (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function update_type (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function delete_type (ServerRequestInterface $request, ResponseInterface $response, $args) {
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
