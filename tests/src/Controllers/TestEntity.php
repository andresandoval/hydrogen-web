<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 03/12/2017
 * Time: 12:01
 */

namespace Hydrogen\Web\Tests\Controllers;


class TestEntity {

    private $name;
    private $lastName;
    private $other;

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getOther() {
        return $this->other;
    }

    /**
     * @param mixed $other
     */
    public function setOther($other): void {
        $this->other = $other;
    }



}