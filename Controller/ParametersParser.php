<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\ResourceBundle\Controller;

use Sylius\Bundle\ResourceBundle\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Configuration parameters parser.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@sylius.pl>
 */
class ParametersParser
{
    /**
     * @var ExpressionLanguage
     */
    private $expression;

    /**
     * @param ExpressionLanguage $expression
     */
    public function __construct(ExpressionLanguage $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @param array   $parameters
     * @param Request $request
     *
     * @return array
     */
    public function parseRequestValues(array &$parameters, Request $request)
    {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                 $this->parseRequestValues($value, $request);
            }

            if (is_string($value) && 0 === strpos($value, '$')) {
                $parameterName = substr($value, 1);
                var_dump($request->get($parameterName));
                $parameters[$key] = $request->get($parameterName);
            }

            if (is_string($value) && 0 === strpos($value, 'expr:')) {
                $parameters[$key] = $this->expression->evaluate(substr($value, 5));
            }
        }

        return $parameters;
    }
}
