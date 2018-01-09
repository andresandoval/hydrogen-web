<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 10/12/2017
 * Time: 17:12
 */

namespace Hydrogen\Web\Model\Metadata\Controller;


class RequestMappingVariable {

    private $from = null;
    private $to = null;

    public function __construct() {
    }

    /**
     * @return mixed
     */
    public function getFrom(): ?string {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from): void {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getTo(): ?string {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo(string $to): void {
        $this->to = $to;
    }

    public function getNameToMap(): string {
        if(!\is_null($this->to))
            return $this->to;
        return $this->from;
    }


}