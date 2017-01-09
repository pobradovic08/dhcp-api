<?php

namespace Dhcp\Group;

class GroupEntry {

    protected $group_id;
    protected $subnet_id;
    protected $group_name;
    protected $group_description;

    public function __construct (array $data) {
        // If there's group_id defined it already exists in DB
        if (isset($data['group_id'])) {
            // Check if ID is integer and greater than zero
            if (!is_int ($data['group_id']) or $data['group_id'] <= 0) {
                throw new \InvalidArgumentException("ID is invalid");
            } else {
                $this->group_id = (int)$data['group_id'];
            }
        }
        // Check for required parameters and throw exception if needed
        if (!isset($data['group_subnet_id'], $data['group_name'], $data['group_description'])) {
            throw new \InvalidArgumentException("Missing arguments.");
        }
        // Check if ID is integer and greater than zero
        if (!is_int ($data['group_subnet_id']) or $data['group_subnet_id'] <= 0) {
            throw new \InvalidArgumentException("Subnet ID is invalid");
        } else {
            $this->subnet_id = (int)$data['group_subnet_id'];
        }
        // Check if group_name matches pattern
        if (!preg_match ('/^[a-zA-Z0-9-_\.]+$/', (string)$data['group_name'])) {
            throw new \InvalidArgumentException("Group name can only consist of alphanumeric and _ - . characters");
        } else {
            $this->group_name = strtolower ($data['group_name']);
        }
        // Check if group description is le than 64 characters
        if (strlen ($data['group_description']) > 64) {
            throw new \InvalidArgumentException("Description is too long");
        } else {
            $this->group_description = (string)$data['group_description'];
        }
    }

    /**
     * @return int
     */
    public function getId () {
        return $this->group_id;
    }

    /**
     * @return int
     */
    public function getSubnetId () {
        return $this->subnet_id;
    }

    /**
     * @return string
     */
    public function getName () {
        return $this->group_name;
    }

    /**
     * @return string
     */
    public function getDescription () {
        return $this->group_description;
    }

    public function serialize () {
        return [
            'group_id' => $this->getId (),
            'group_subnet_id' => $this->getSubnetId (),
            'group_name' => $this->getName (),
            'group_description' => $this->getDescription (),
        ];
    }
}
