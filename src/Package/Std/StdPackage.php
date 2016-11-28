<?php

namespace Krak\Mw\Http\Package\Std;

use Krak\Mw\Http;

class StdPackage implements Http\Package
{
    private $config;

    public function __construct(array $config = []) {
        $this->config = $config + [
            'controller_prefix' => '',
            'controller_method_separater' => '@',
            'request_attribute_name' => 'app',
        ];
    }

    public function with(Http\App $app) {
        $app->register(new StdServiceProvider());

        $app['stacks.exception_handler']
            ->push(stdExceptionHandler($app['response_factory']));
        $app['stacks.not_found_handler']
            ->push(stdNotFoundHandler($app['response_factory']));
        $app['stacks.marshal_response']
            ->push(stringMarshalResponse())
            ->unshift(redirectMarshalResponse(), 1)
            ->unshift(httpTupleMarshalResponse(), 1);
        $app['stacks.invoke_action']
            ->push(callableInvokeAction(), 0, 'invoke')
            ->push(pimpleInvokeAction(
                $app->getContainer(),
                $this->config['controller_prefix'],
                $this->config['controller_method_separater']
            ));

        $app->push(Http\injectRequestAttribute(
            $this->config['request_attribute_name'],
            $app
        ), 1);
    }
}
