<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 25/11/2017
 * Time: 14:59
 */

namespace Hydrogen\Web\Tests;


use Hydrogen\Web\HydrogenWebApp;
use Hydrogen\Web\Model\Metadata\Controller\RequestMapping;


class Main extends HydrogenWebApp {

    public function __construct(array $controllers) {
        parent::__construct($controllers, true);
    }

    protected function prepareApp() {
    }

    protected function handleSessionSecurity(RequestMapping $methodMetadata): bool {
        return true;
    }

    protected function handleCustomSecurity(RequestMapping $methodMetadata): bool {
        return true;
    }
}