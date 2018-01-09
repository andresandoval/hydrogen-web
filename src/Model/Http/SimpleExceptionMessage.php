<?php
/**
 * Created by PhpStorm.
 * User: asandoval
 * Date: 05/12/2017
 * Time: 12:25
 */

namespace Hydrogen\Web\Model\Http;

final class SimpleExceptionMessage {

    private $code;
    private $definition;
    private $detail;
    private $status;

    /**
     * SimpleExceptionMessage constructor.
     * @param $code
     * @param $definition
     * @param $detail
     * @param $status
     */
    public function __construct($code, $definition, $detail, $status) {
        $this->code = $code;
        $this->definition = $definition;
        $this->detail = $detail;
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getDefinition() {
        return $this->definition;
    }

    /**
     * @return mixed
     */
    public function getDetail() {
        return $this->detail;
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        return $this->status;
    }

}