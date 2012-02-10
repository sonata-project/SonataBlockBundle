<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\BlockBundle\Block;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * BaseBlockService
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseBlockService implements BlockServiceInterface
{
    protected $name;

    protected $templating;

    /**
     * @param $name
     * @param \Symfony\Component\Templating\EngineInterface $templating
     */
    public function __construct($name, EngineInterface $templating)
    {
        $this->name = $name;
        $this->templating = $templating;
    }

    /**
     * Render a view
     *
     * @param string $view
     * @param array $parameters
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return string
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        return $this->getTemplating()->renderResponse($view, $parameters, $response);
    }

    /**
     * Get name
     *
     * @return string name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get templating
     *
     * @return \Symfony\Component\Templating\EngineInterface
     */
    public function getTemplating()
    {
        return $this->templating;
    }

    /**
     * Build form
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block)
    {
        $this->buildEditForm($formMapper, $block);
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    public function prePersist(BlockInterface $block)
    {
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    public function postPersist(BlockInterface $block)
    {
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    public function preUpdate(BlockInterface $block)
    {
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    public function postUpdate(BlockInterface $block)
    {
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    public function preDelete(BlockInterface $block)
    {
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    public function postDelete(BlockInterface $block)
    {
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    public function load(BlockInterface $block)
    {
    }

    /**
     * @return array
     */
    function getJavacripts($media)
    {
        return array();
    }

    /**
     * @return array
     */
    function getStylesheets($media)
    {
        return array();
    }
}