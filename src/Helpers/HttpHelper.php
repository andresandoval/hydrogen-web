<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 25/11/2017
 * Time: 10:56
 */

namespace Hydrogen\Web\Helpers;



use Helium\Mapper\ObjectToJson;
use Hydrogen\Web\Model\Http\ResponseEntity;
use Hydrogen\Web\Values\ConsumesType;
use Hydrogen\Web\Values\HttpStatus;
use Lithium\Exceptions\NullPointerException;
use Lithium\Exceptions\UnsupportedTypeException;

final class HttpHelper {


    /**
     * @param string $httpMethod Método para leer la entrada POST, GET, PUT, etc
     * @param string $consumes Tipo de contenido a leer JSON ó XFORM de acuerdo a ConsumesType
     * @return array|null
     * @throws UnsupportedTypeException
     * @throws NullPointerException
     */
    public static function readHttpRequest(string $httpMethod, string $consumes): ?array {
        if (\is_null($httpMethod))
            throw new NullPointerException("Could not read input buffer: RequestMethod is not defined");
        if (\is_null($consumes))
            throw new NullPointerException("Could not read input buffer: RequestConsumeType is not defined");
        if (!ConsumesType::validate($consumes))
            throw new UnsupportedTypeException("Could not read input buffer: RequestConsumeType is unknown or not supported yet");

        if ($httpMethod == "GET" || ($httpMethod == "POST" && $consumes == ConsumesType::XFORM)) {
            $bodyArray = \filter_input_array($httpMethod == "GET" ? \INPUT_GET : \INPUT_POST);
            if (\is_null($bodyArray) || !\is_array($bodyArray))
                return null;
            return $bodyArray;
        }
        $jsonString = \file_get_contents("php://input");
        if ($jsonString == false || \is_null($jsonString) || !\is_string($jsonString) ||
            \strlen(\trim($jsonString)) <= 0)
            return null;
        if ($consumes == ConsumesType::XFORM) {
            \parse_str($jsonString, $xFormArray);
            if (\is_null($xFormArray) || !\is_array($xFormArray))
                return null;
            return $xFormArray;
        }

        $jsonArray = \json_decode($jsonString, true);
        if (\is_null($jsonArray) || !\is_array($jsonArray))
            return null;
        return $jsonArray;
    }

    /**
     * @param mixed $outputData
     * @param string $produces
     * @throws NullPointerException
     * @throws UnsupportedTypeException
     */
    public static function writeHttpResponse($outputData, string $produces) {
        if (\is_null($produces))
            throw new NullPointerException("Could not write output buffer: Produces type is not defined");
        $httpStatus = HttpStatus::OK;
        $outputBody = $outputData;

        if (!\is_null($outputData) && $outputData instanceof ResponseEntity) {
            $httpStatus = $outputData->getHttpStatus();
            $outputBody = $outputData->getBody();
        }
        if (\is_array($outputBody) || \is_object($outputBody))
            $outputBody = ObjectToJson::map($outputBody);
        else if (!\is_scalar($outputBody) && !\is_null($outputBody))
            throw new UnsupportedTypeException("Could not write output buffer: Unsupported type " .
                \gettype($outputBody) . " for outcome body");

        \http_response_code($httpStatus);
        \header("Access-Control-Expose-Headers: X-Handled-By");
        \header("X-Handled-By: hydrogen-framework");
        \header("Content-Type: $produces");
        echo (string)$outputBody;
    }
}