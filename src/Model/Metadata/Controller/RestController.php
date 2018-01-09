<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 10/12/2017
 * Time: 17:02
 */

namespace Hydrogen\Web\Model\Metadata\Controller;


class RestController {

    private $path = "";

    public function __construct() {
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void {
        $this->path = $path;
    }



}