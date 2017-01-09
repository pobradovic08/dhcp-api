<?php
namespace Dhcp\EndHostType;

class EndHostTypeEntry {

    private $id;
    private $description;

    public function __construct (array $data) {
        if (isset($data['end_host_type_id'])) {
            if (!is_int ($data['end_host_type_id']) or $data['end_host_type_id'] <= 0) {
                throw new \InvalidArgumentException("ID invalid");
            }
            $this->id = $data['end_host_type_id'];
        }
        if (!isset($data['end_host_type_description'])) {
            throw new \InvalidArgumentException("Missing required data.");
        }
        if (strlen ($data['end_host_type_description']) <= 64) {
            $this->description = (string)$data['end_host_type_description'];
        } else {
            throw new \InvalidArgumentException("Description too long");
        }
    }

    public function getId () {
        return $this->id;
    }

    public function getDescription () {
        return $this->description;
    }

    public function serialize () {
        return [
            'end_host_type_id' => $this->getId (),
            'end_host_type_description' => $this->getDescription (),
        ];
    }
}
