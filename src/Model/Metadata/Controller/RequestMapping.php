<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 10/12/2017
 * Time: 17:04
 */

namespace Hydrogen\Web\Model\Metadata\Controller;


use Hydrogen\Web\Values\ConsumesType;
use Hydrogen\Web\Values\ProducesType;
use Hydrogen\Web\Values\SecurityType;

class RequestMapping {

    private $value = "";
    private $method = "GET";
    private $consumes = ConsumesType::JSON;
    private $produces = ProducesType::JSON;
    private $security = SecurityType::NONE;
    private $pathVariable = null;
    private $requestParam = null;
    private $requestBody = null;
    private $fullDefinitionPath = null;
    private $fullInputPath = null;

    public function __construct() {
    }

    /**
     * @return string
     */
    public function getValue(): string {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getConsumes(): string {
        return $this->consumes;
    }

    /**
     * @param string $consumes
     */
    public function setConsumes(string $consumes): void {
        $this->consumes = $consumes;
    }

    /**
     * @return string
     */
    public function getProduces(): string {
        return $this->produces;
    }

    /**
     * @param string $produces
     */
    public function setProduces(string $produces): void {
        $this->produces = $produces;
    }

    /**
     * @return string
     */
    public function getSecurity(): string {
        return $this->security;
    }

    /**
     * @param string $security
     */
    public function setSecurity(string $security): void {
        $this->security = $security;
    }

    /**
     * @return RequestMappingVariable[]
     */
    public function getPathVariable(): ?array {
        return $this->pathVariable;
    }

    /**
     * @param RequestMappingVariable[] $pathVariable
     */
    public function setPathVariable(RequestMappingVariable ...$pathVariable): void {
        $this->pathVariable = $pathVariable;
    }

    /**
     * @return RequestMappingVariable[]
     */
    public function getRequestParam():?array {
        return $this->requestParam;
    }

    /**
     * @param RequestMappingVariable[] $requestParam
     */
    public function setRequestParam(RequestMappingVariable ...$requestParam): void {
        $this->requestParam = $requestParam;
    }

    /**
     * @return RequestMappingVariable
     */
    public function getRequestBody(): ?RequestMappingVariable {
        return $this->requestBody;
    }

    /**
     * @param RequestMappingVariable $requestBody
     */
    public function setRequestBody(RequestMappingVariable $requestBody): void {
        $this->requestBody = $requestBody;
    }

    /**
     * @return string
     */
    public function getFullDefinitionPath():string {
        return $this->fullDefinitionPath;
    }

    /**
     * @param string $fullDefinitionPath
     */
    public function setFullDefinitionPath(string $fullDefinitionPath): void {
        $this->fullDefinitionPath = $fullDefinitionPath;
    }

    /**
     * @return string
     */
    public function getFullInputPath(): string {
        return $this->fullInputPath;
    }

    /**
     * @param string $fullInputPath
     */
    public function setFullInputPath(string $fullInputPath): void {
        $this->fullInputPath = $fullInputPath;
    }


}