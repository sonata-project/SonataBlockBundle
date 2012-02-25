<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Twig\Extension;

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Model\BlockRepositoryInterface;

class BlockExtension extends \Twig_Extension
{
    private $blockServiceManager;
    private $blockRepository;

    private $environment;

    /**
     * @param \Sonata\BlockBundle\Model\BlockManagerInterface $blockServiceManager
     * @param \Sonata\BlockBundle\Model\BlockRepositoryInterface $blockRepository
     */
    public function __construct(BlockServiceManagerInterface $blockServiceManager, BlockRepositoryInterface $blockRepository)
    {
        $this->blockServiceManager = $blockServiceManager;
        $this->blockRepository = $blockRepository;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'sonata_block_render'  => new \Twig_Function_Method($this, 'renderBlock', array('is_safe' => array('html'))),
            'sonata_block_include_javascripts'  => new \Twig_Function_Method($this, 'includeJavascripts', array('is_safe' => array('html'))),
            'sonata_block_include_stylesheets'  => new \Twig_Function_Method($this, 'includeStylesheets', array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sonata_block';
    }

    /**
     * @param $media screen|all ....
     * @return array|string
     */
    public function includeJavascripts($media)
    {
        $javascripts = array();

        foreach ($this->blockServiceManager->getLoadedBlockServices() as $service) {
            $javascripts = array_merge($javascripts, $service->getJavacripts($media));
        }

        if (count($javascripts) == 0) {
            return '';
        }

        $html = "";
        foreach ($javascripts as $javascript) {
            $html .= "\n" . sprintf('<script src="%s" type="text/javascript"></script>', $javascript);
        }

        return $html;
    }

    /**
     * @param $media
     * @return array|string
     */
    public function includeStylesheets($media)
    {
        $stylesheets = array();

        foreach ($this->blockServiceManager->getLoadedBlockServices() as $service) {
            $stylesheets = array_merge($stylesheets, $service->getStylesheets($media));
        }

        if (count($stylesheets) == 0) {
            return '';
        }

        $html = sprintf("<style type='text/css' media='%s'>", $media);

        foreach ($stylesheets as $stylesheet) {
            $html .= "\n" . sprintf('@import url(%s);', $stylesheet, $media);
        }

        $html .= "\n</style>";

        return $html;
    }

    /**
     * @param $name
     * @return string
     */
    public function renderBlock($name)
    {
        $block = $this->blockRepository->findOneBy(array('name' => $name));
        if ($block) {
            return $this->blockServiceManager->renderBlock($block)->getContent();
        } else {
            return '';
        }
    }
}

