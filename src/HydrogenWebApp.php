<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 25/11/2017
 * Time: 10:19
 */

namespace Hydrogen\Web;

use Hydrogen\Web\Exceptions\EndpointException;
use Hydrogen\Web\Exceptions\RouterException;
use Hydrogen\Web\Helpers\EndpointHelper;
use Hydrogen\Web\Helpers\RegexpHelper;
use Hydrogen\Web\Model\Metadata\Controller\RequestMapping;
use Hydrogen\Web\Model\Metadata\Controller\RestController;
use Hydrogen\Web\Values\SecurityType;
use Lithium\Exceptions\ClassNotFoundException;
use Lithium\Exceptions\UnsupportedTypeException;
use Lithium\Worker\DocMetadata;

abstract class HydrogenWebApp {

    private $controllers;
    private $fallbackEndpointMeta;
    private $httpMethod;
    private $requestPath;

    /**
     * HydrogenWebApp constructor.
     * @param      $controllers
     * @param bool $dev
     */
    public function __construct($controllers, $dev = false) {
        $this->controllers = $controllers;
        $this->httpMethod = $httpMethod = $_SERVER["REQUEST_METHOD"];
        $this->requestPath = $_SERVER["PATH_INFO"] ?? "/";

        $this->prepareApp();
        $this->fallbackEndpointMeta = new RequestMapping();
        if (!$dev) {
            \set_error_handler(function ($errNumber, $errString, $errfile, $errline, array $errcontext) {
                throw new \Exception($errString, $errNumber);
            });
        }
    }

    protected abstract function prepareApp();

    protected abstract function handleSessionSecurity(RequestMapping $methodMetadata): bool;

    protected abstract function handleCustomSecurity(RequestMapping $methodMetadata): bool;

    private final function handleSecurity(RequestMapping $requestMappingMetadata): bool {
        if (\is_null($requestMappingMetadata->getSecurity()) ||
            $requestMappingMetadata->getSecurity() == SecurityType::NONE)
            return true;
        if ($requestMappingMetadata->getSecurity() == SecurityType::SESSION)
            return $this->handleSessionSecurity($requestMappingMetadata);
        else if ($requestMappingMetadata->getSecurity() == SecurityType::CUSTOM)
            return $this->handleCustomSecurity($requestMappingMetadata);
        else
            return false;
    }

    /**
     * @param \ReflectionMethod $method
     * @param string            $controllerPrefix
     * @return RequestMapping|null
     * @throws ClassNotFoundException
     * @throws \Lithium\Exceptions\MissingParameterException
     * @throws \Lithium\Exceptions\NullPointerException
     */
    private final function resolveRestControllerMethod(\ReflectionMethod $method,
                                                       string $controllerPrefix): ?RequestMapping {
        /** @var RequestMapping $requestMappingMetadata */
        $requestMappingMetadata = DocMetadata::get($method->getDocComment(), RequestMapping::class);
        if (\is_null($requestMappingMetadata))
            return null;
        $fullMethodDefinitionPath = "$controllerPrefix{$requestMappingMetadata->getValue()}";
        if (RegexpHelper::checkRestControllerMethodPathMatch($fullMethodDefinitionPath, $this->requestPath)) {
            $requestMappingMetadata->setFullDefinitionPath($fullMethodDefinitionPath);
            $requestMappingMetadata->setFullInputPath($this->requestPath);
            return $requestMappingMetadata;
        }
        return null;
    }

    /**
     * @param string $controllerClassName
     * @return EndpointHelper|null
     * @throws ClassNotFoundException
     * @throws \Lithium\Exceptions\MissingParameterException
     * @throws \Lithium\Exceptions\NullPointerException
     */
    private final function resolveRestController(string $controllerClassName): ?EndpointHelper {
        /** @var RestController $restControllerMetadata */

        if (!\class_exists($controllerClassName))
            throw new ClassNotFoundException("Could not resolve controller: Class $controllerClassName not exists");
        $restControllerReflection = new \ReflectionClass($controllerClassName);
        $restControllerDoc = $restControllerReflection->getDocComment();
        $restControllerMetadata =
            ($restControllerDoc != false) ? DocMetadata::get($restControllerDoc, RestController::class) : null;
        if (\is_null($restControllerMetadata))
            return null;
        $controllerPath = $restControllerMetadata->getPath();
        if (\strlen($controllerPath) > 0) {
            if (!RegexpHelper::checkRestControllerPathMatch($controllerPath, $this->requestPath))
                return null;
        }
        $restControllerMethods = $restControllerReflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $restControllerMethodsWithDoc = \array_filter($restControllerMethods,
            function (\ReflectionMethod $method) {
                return $method->getDocComment() != false;
            });
        foreach ($restControllerMethodsWithDoc as $restControllerMethodReflection) {
            $requestMappingMetadata =
                $this->resolveRestControllerMethod($restControllerMethodReflection, $controllerPath);
            if (!\is_null($requestMappingMetadata))
                return new EndpointHelper($restControllerMetadata,
                    $restControllerReflection,
                    $requestMappingMetadata,
                    $restControllerMethodReflection);
        }
        return null;
    }

    /**
     * @return EndpointHelper|null
     * @throws ClassNotFoundException
     * @throws RouterException
     * @throws \Lithium\Exceptions\MissingParameterException
     * @throws \Lithium\Exceptions\NullPointerException
     */
    private final function resolveEndPoint(): ?EndpointHelper {
        if (\is_null($this->controllers) || !\is_array($this->controllers) || \count($this->controllers) <= 0)
            throw new RouterException("Could not resolve route: No controllers provided");
        foreach ($this->controllers as $restControllerClassName) {
            $result = $this->resolveRestController($restControllerClassName);
            if (!\is_null($result) && $result->getRequestMappingMetadata()->getMethod() == $this->httpMethod)
                return $result;
        }
        return null;
    }

    /**
     * @throws UnsupportedTypeException
     * @throws \Lithium\Exceptions\NullPointerException
     */
    public final function run() {
        try {
            $result = $this->resolveEndPoint();
            if (\is_null($result)) {
                EndpointHelper::notFound("No entry found");
                return;
            }
            if (!$this->handleSecurity($result->getRequestMappingMetadata())) {
                EndpointHelper::unauthorized();
                return;
            }
            $result->process();
        } catch (EndpointException $implementationException) {
            EndpointHelper::serverError("Internal endpoint error");
        } catch (\Throwable $exception) {
            EndpointHelper::serverError($exception->getMessage());
        }
    }
}