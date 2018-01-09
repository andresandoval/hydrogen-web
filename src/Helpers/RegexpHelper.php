<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 25/11/2017
 * Time: 12:53
 */

namespace Hydrogen\Web\Helpers;

final class RegexpHelper {

    /**
     * @param string $componentPath
     * @param string $inputPath
     * @return bool
     */
    public static function checkRestControllerPathMatch(string $componentPath, string $inputPath): bool {
        $componentPath = "$componentPath.*";
        $componentPath = \preg_replace("/\//", "\/", $componentPath);
        return \preg_match("/$componentPath/", $inputPath) != false;
    }

    /**
     * @param string $methodPath
     * @param string $inputPath
     * @return bool
     */
    public static function checkRestControllerMethodPathMatch(string $methodPath, string $inputPath): bool {
        $methodPath = \preg_replace('/\?.*$/', "", $methodPath);
        $methodPath = \preg_replace("/\//", "\/", $methodPath);
        $methodPath = \preg_replace("/\{[^\}]+\}/", "[^\/]+", $methodPath);
        return \preg_match("/^$methodPath$/", $inputPath) != false;
    }

    /**
     * @param string $methodPart
     * @return null|string
     */
    private static function getPathVariableName(string $methodPart): ?string {
        if (\preg_match("/^\{[^\}]+\}$/", $methodPart) == false)
            return null;
        return \preg_replace("/[\{\}]/", "", $methodPart);
    }

    /**
     * @param string $definitionPath
     * @param string $inputPath
     * @return array|null
     */
    public static function getInputPathVariables(string $definitionPath, string $inputPath): ?array {
        if (\is_null($definitionPath) || \is_null($inputPath) || !\is_string($definitionPath) ||
            !\is_string($inputPath) || \strlen($definitionPath) <= 0 || \strlen($inputPath) <= 0
        )
            return null;
        $definitionPath = \preg_replace("/^\//", "", $definitionPath);
        $inputPath = \preg_replace("/^\//", "", $inputPath);

        $definitionPathArray = \preg_split("/\//", $definitionPath);
        $inputPathArray = \preg_split("/\//", $inputPath);
        if ($definitionPathArray == false || $inputPathArray == false || !\is_array($definitionPathArray) ||
            !\is_array($inputPathArray) || \count($definitionPathArray) != \count($inputPathArray)
        )
            return null;
        $variableArray = [];
        for ($i = 0; $i < \count($definitionPathArray); $i++) {
            $tmpVariableName = self::getPathVariableName($definitionPathArray[$i]);
            if (!\is_null($tmpVariableName))
                $variableArray[$tmpVariableName] = $inputPathArray[$i];
        }
        return $variableArray;
    }
}