<?php

namespace Krak\Mw\Http\Package\AutoArgs;

use Krak\Mw\Http;
use Krak\Mw;

class AutoArgsFreezer implements Http\Freezer
{
    private $freezer;

    public function __construct(Http\Freezer $freezer) {
        $this->freezer = $freezer;
    }

    public function freezeApp(Http\App $app) {
        $resolve_arg = Mw\compose([$app['stacks.resolve_argument']]);
        $app['stacks.invoke_action']->push(
            resolveArgumentsCallableInvokeInvokeAction($resolve_arg),
            0,
            'invoke'
        );

        return $this->freezer->freezeApp($app);
    }
}
