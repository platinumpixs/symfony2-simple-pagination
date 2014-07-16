<?php
/**
 * Platinum Pixs
 *
 * @copyright  Copyright (c) 2010-2014, Platinum Pixs, LLC All rights reserved.
 * @link       http://www.platinumpixs.com
 */

namespace PlatinumPixs\SimplePagination\DependencyInjection;

/**
 * XXX
 *
 * @copyright  Copyright (c) 2010-2014, Platinum Pixs, LLC All rights reserved.
 * @link       http://www.platinumpixs.com
 */
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
            new \Twig_SimpleFunction('grid_set_pagination_path', array($this, 'setPaginationPath')),
            new \Twig_SimpleFunction('platinum_pixs_simple_pagination', array($this, 'getPagination'), array('is_safe' => array('html')))
        );
    }

    public function getPagination($grid)
    {
        return $this->renderBlock('pagination', array('grid' => $grid));
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
        //load template if needed
        if (is_null($this->template))
        {
            //get template name
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
 