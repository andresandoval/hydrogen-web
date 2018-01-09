<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 25/11/2017
 * Time: 21:40
 */

namespace Hydrogen\Web\Helpers;


use Helium\Mapper\ArrayToObject;
use Hydrogen\Web\Exceptions\EndpointException;
use Hydrogen\Web\Model\Http\ResponseEntity;
use Hydrogen\Web\Model\Metadata\Controller\RequestMapping;
use Hydrogen\Web\Model\Metadata\Controller\RequestMappingVariable;
use Hydrogen\Web\Model\Metadata\Controller\RestController;
use Hydrogen\Web\Values\HttpStatus;
use Hydrogen\Web\Values\ProducesType;
use Lithium\Exceptions\MissingParameterException;

final class EndpointHelper {

    private $restControllerMetadata;
    private $restControllerReflectionObj;
    private $requestMappingMetadata;
    private $requestMappingReflectionObj;

    /**
     * EndpointEntity constructor.
     * @param RestController    $restControllerMetadata
     * @param \ReflectionClass  $restControllerReflectionObj
     * @param RequestMapping    $requestMappingMetadata
     * @param \ReflectionMethod $requestMappingReflectionObj
     */
    public function __construct($restControllerMetadata, $restControllerReflectionObj, $requestMappingMetadata,
                                $requestMappingReflectionObj) {
        $this->restControllerMetadata = $restControllerMetadata;
        $this->restControllerReflectionObj = $restControllerReflectionObj;
        $this->requestMappingMetadata = $requestMappingMetadata;
        $this->requestMappingReflectionObj = $requestMappingReflectionObj;
    }

    /**
     * @return RestController
     */
    public function getRestControllerMetadata(): RestController {
        return $this->restControllerMetadata;
    }

    /**
     * @return \ReflectionClass
     */
    public function getRestControllerReflectionObj(): \ReflectionClass {
        return $this->restControllerReflectionObj;
    }

    /**
     * @return RequestMapping
     */
    public function getRequestMappingMetadata(): RequestMapping {
        return $this->requestMappingMetadata;
    }

    /**
     * @return \ReflectionMethod
     */
    public function getRequestMappingReflectionObj(): \ReflectionMethod {
        return $this->requestMappingReflectionObj;
    }

    /**
     * @param string $body
     * @throws \Lithium\Exceptions\NullPointerException
     * @throws \Lithium\Exceptions\UnsupportedTypeException
     */
    public static function serverError($body = "Internal server error") {
        $response = new ResponseEntity(HttpStatus::INTERNAL_SERVER_ERROR, $body);
        HttpHelper::writeHttpResponse($response, ProducesType::HTML);
    }

    /**
     * @param string $body
     * @throws \Lithium\Exceptions\NullPointerException
     * @throws \Lithium\Exceptions\UnsupportedTypeException
     */
    public static function notFound($body = "Not found") {
        $response = new ResponseEntity(HttpStatus::NOT_FOUND, $body);
        HttpHelper::writeHttpResponse($response, ProducesType::HTML);
    }

    /**
     * @param string $body
     * @throws \Lithium\Exceptions\NullPointerException
     * @throws \Lithium\Exceptions\UnsupportedTypeException
     */
    public static function unauthorized($body = "Unauthorized") {
        $response = new ResponseEntity(HttpStatus::UNAUTHORIZED, $body);
        HttpHelper::writeHttpResponse($response, ProducesType::HTML);
    }

    /**
     * @return array
     * @throws MissingParameterException
     * @throws \Lithium\Exceptions\ClassNotFoundException
     * @throws \Lithium\Exceptions\NullPointerException
     * @throws \Lithium\Exceptions\UnsupportedTypeException
     */
    private function getEndpointParameters(): array {
        /** @var RequestMappingVariable */
        /** @var RequestMappingVariable[] $filteredPathVariablesInMetadata */
        /** @var RequestMappingVariable $requestParameterInMetadata */

        $allParameters = [];

        /* Get  variables in request path */
        $inputPathVariables =
            RegexpHelper::getInputPathVariables($this->requestMappingMetadata->getFullDefinitionPath(),
                $this->requestMappingMetadata->getFullInputPath());
        if (!\is_null($inputPathVariables) && \count($inputPathVariables) > 0) {
            $pathVariablesInMetadata = $this->requestMappingMetadata->getPathVariable();
            if (!\is_null($pathVariablesInMetadata) && \is_array($pathVariablesInMetadata) &&
                \count($pathVariablesInMetadata) > 0) {
                foreach ($inputPathVariables as $inputPathVariableKey => $inputPathVariableValue) {
                    $filteredPathVariablesInMetadata = \array_values(\array_filter($pathVariablesInMetadata,
                        function (RequestMappingVariable $val) use ($inputPathVariableKey) {
                            return $val->getFrom() == $inputPathVariableKey;
                        }));
                    if (!\is_null($filteredPathVariablesInMetadata) && \count($filteredPathVariablesInMetadata) > 0) {
                        $pathVariableMetadata = $filteredPathVariablesInMetadata[0];
                        $variableName = $pathVariableMetadata->getNameToMap();
                        if (!isset($allParameters[$variableName]))
                            $allParameters[$variableName] = $inputPathVariableValue;
                    }
                }
            }
        }

        /* Get  variables in request parameters */
        $requestParameterValues = \filter_input_array(\INPUT_GET);
        if (\is_array($requestParameterValues) && \count($requestParameterValues) > 0) {
            $requestParametersInMetadata = $this->requestMappingMetadata->getRequestParam();
            if (\is_array($requestParametersInMetadata) && \count($requestParametersInMetadata) > 0) {
                foreach ($requestParametersInMetadata as $requestParameterInMetadata) {
                    $variableName = $requestParameterInMetadata->getNameToMap();
                    if (isset($requestParameterValues[$variableName]) && !isset($allParameters[$variableName]))
                        $allParameters[$variableName] = $requestParameterValues[$variableName];
                }
            }
        }

        /* Get request body */
        $requestBodyMetadata = $this->requestMappingMetadata->getRequestBody();
        if (!\is_null($requestBodyMetadata)) {
            $variableName = $requestBodyMetadata->getTo();
            if (!\is_null($variableName) && !isset($allParameters[$variableName]))
                $allParameters[$variableName] = HttpHelper::readHttpRequest($this->requestMappingMetadata->getMethod(),
                    $this->requestMappingMetadata->getConsumes());
        }

        $finalParameters = [];
        $reflectionParameters = $this->requestMappingReflectionObj->getParameters();
        foreach ($reflectionParameters as $reflectionParameter) {
            $index = $reflectionParameter->getPosition();
            if (isset($allParameters[$reflectionParameter->getName()])) {
                if ($reflectionParameter->hasType() && !\is_null($reflectionParameter->getClass())) {
                    $parameterClassName = $reflectionParameter->getClass()->getName();
                    $parameterValue =
                        ArrayToObject::map($allParameters[$reflectionParameter->getName()], $parameterClassName);
                    if (!\is_null($parameterValue)) {
                        $finalParameters[$index] = $parameterValue;
                        continue;
                    }
                } else {
                    $finalParameters[$index] = $allParameters[$reflectionParameter->getName()];
                    continue;
                }
            }
            if ($reflectionParameter->isOptional()) {
                if ($reflectionParameter->isDefaultValueAvailable())
                    $finalParameters[$index] = $reflectionParameter->getDefaultValue();
                else if ($reflectionParameter->isDefaultValueConstant())
                    $finalParameters[$index] = $reflectionParameter->getDefaultValueConstantName();
                else
                    throw new MissingParameterException("Missing default value for parameter " .
                        "{$reflectionParameter->getName()} in {$this->requestMappingReflectionObj->getName()}");
            } else if ($reflectionParameter->allowsNull()) {
                $finalParameters[$index] = null;
            } else {
                throw new MissingParameterException("Missing parameter  {$reflectionParameter->getName()} " .
                    "in {$this->requestMappingReflectionObj->getName()}");
            }
        }
        return $finalParameters;
    }

    /**
     * @param null $args
     * @return mixed
     * @throws EndpointException
     */
    private function invokeEndpoint($args = null) {
        \ob_start();
        try {
            $obj = $this->restControllerReflectionObj->newInstance();
            return \is_null($args) ? $this->requestMappingReflectionObj->invoke($obj) :
                $this->requestMappingReflectionObj->invokeArgs($obj,
                    $args);
        } catch (\Throwable $exception) {
            throw new EndpointException("Error executing endpoint", $exception->getCode(), $exception);
        } finally {
            \ob_end_clean();
        }
    }

    /**
     * @throws EndpointException
     * @throws MissingParameterException
     * @throws \Lithium\Exceptions\ClassNotFoundException
     * @throws \Lithium\Exceptions\NullPointerException
     * @throws \Lithium\Exceptions\UnsupportedTypeException
     */
    public function process() {
        $classConstructor = $this->restControllerReflectionObj->getConstructor();
        if (!\is_null($classConstructor) && $classConstructor->getNumberOfRequiredParameters() > 0)
            throw new MissingParameterException("Could not process endpoint request: To many arguments in " .
                "{$this->restControllerReflectionObj->getName()} class constructor");
        unset($classConstructor);
        $endpointResult = null;
        if ($this->requestMappingReflectionObj->getNumberOfParameters() <= 0) {
            $endpointResult = $this->invokeEndpoint();
        } else {
            $parameters = $this->getEndpointParameters();
            if (\is_null($parameters) || !\is_array($parameters) ||
                \count($parameters) != $this->requestMappingReflectionObj->getNumberOfParameters())
                throw new MissingParameterException("Could not process endpoint request: To many arguments in " .
                    "{$this->restControllerReflectionObj->getName()}->{$this->requestMappingReflectionObj->getName()} method");
            $endpointResult = $this->invokeEndpoint($parameters);
        }
        HttpHelper::writeHttpResponse($endpointResult, $this->requestMappingMetadata->getProduces());
    }

}