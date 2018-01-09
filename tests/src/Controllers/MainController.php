<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 25/11/2017
 * Time: 15:22
 */

namespace Hydrogen\Web\Tests\Controllers;

/**
 * Class MainController
 * @package Custom\Controllers
 *
 * @RestController(
 *     @path /foo
 * )
 */
class MainController {


    /**
     * @RequestMapping(
     *     @value /get/{name}/items?lastName={lastName}
     *     @method POST
     *     @security SESSION
     *     @produces JSON
     *     @PathVariable(@from=name)
     *     @RequestParam(@from=lastName)
     *     @RequestBody(@to=body)
     * )
     * @param string     $name
     * @param string     $lastName
     * @param TestEntity $body
     * @return TestEntity
     */
    public function hello(string $name, string $lastName, TestEntity $body) : TestEntity{
        return $body;
        $xx = new TestEntity();
        $xx->setName($name);
        $xx->setLastName($lastName);
        $xx->setOther("other");
        return $xx;
    }


}