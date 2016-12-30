<?php

class GroupEntry {

    protected $group_id;
    protected $subnet_id;
    protected $group_name;
    protected $group_description;

    public function __construct (array $data) {
        // If there's group_id defined it already exists in DB
        if (isset($data['group_id'])) {
            $this->group_id = (int)$data['group_id'];
        }
        // Check for required parameters and throw exception if needed
        if(!isset($data['group_subnet_id'], $data['group_name'], $data['group_description'])){
            throw new InvalidArgumentException("Missing arguments.");
        }
        $this->subnet_id = (int)$data['group_subnet_id'];
        $this->group_name = $data['group_name'];
        $this->group_description = $data['group_description'];
    }

    public function getId () {
        return $this->group_id;
    }

    public function getSubnetId () {
        return $this->subnet_id;
    }

    public function getName () {
        return $this->group_name;
    }

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
