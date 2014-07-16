<?php
/**
 * Copyright 2014 Platinum Pixs, LLC. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace PlatinumPixs\SimplePagination\DependencyInjection;

use PlatinumPixs\SimplePagination\Paginator;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var \Twig_Template
     */
    protected $template;

    /**
     * @var string
     */
    protected $theme;

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'platinumpixs_simple_pagination_extension';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('platinum_pixs_simple_pagination_path', array($this, 'setPaginationPath')),
            new \Twig_SimpleFunction('platinum_pixs_simple_pagination', array($this, 'getPagination'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('platinum_pixs_simple_pagination_count', array($this, 'getDisplaying'), array('is_safe' => array('html')))
        );
    }

    public function setPaginationPath(Paginator $paginator, $pageNumber)
    {
        return $paginator->createUrl($_SERVER['REQUEST_URI'], $pageNumber);
    }

    public function getPagination(Paginator $paginator)
    {
        return $this->renderBlock('pagination', array('paginator' => $paginator));
    }

    public function getDisplaying(Paginator $paginator)
    {
        return $this->renderBlock('displaying', array('paginator' => $paginator));
    }

    /**
     * Render block.
     *
     * @param $name string
     * @param $parameters string
     * @return string
     */
    private function renderBlock($name, $parameters)
    {
        // load template if needed
        if (is_null($this->template))
        {
            // get template name
            if(is_null($this->theme))
            {
                $this->theme = 'PlatinumPixsSimplePaginationBundle::blocks.html.twig';
            }

            $this->template = $this->environment->loadTemplate($this->theme);
        }

        if ($this->template->hasBlock($name))
        {
            return $this->template->renderBlock($name, $parameters);
        }
        else
        {
            throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in template "%s".', $name, $this->theme));
        }
    }
}
 