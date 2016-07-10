<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class AbstractBlockService.
 *
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
abstract class AbstractBlockService implements BlockServiceInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @param string          $name
     * @param EngineInterface $templating
     */
    public function __construct($name, EngineInterface $templating)
    {
        $this->name = $name;
        $this->templating = $templating;
    }

    /**
     * Returns a Response object than can be cacheable.
     *
     * @param string   $view
     * @param array    $parameters
     * @param Response $response
     *
     * @return Response
     */
    final public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        return $this->templating->renderResponse($view, $parameters, $response);
    }

    /**
     * Returns a Response object that cannot be cacheable, this must be used if the Response is related to the user.
     * A good solution to make the page cacheable is to configure the block to be cached with javascript ...
     *
     * @param string   $view
     * @param array    $parameters
     * @param Response $response
     *
     * @return Response
     */
    final public function renderPrivateResponse($view, array $parameters = array(), Response $response = null)
    {
        return $this->renderResponse($view, $parameters, $response)
            ->setTtl(0)
            ->setPrivate()
        ;
    }

    /**
     * {@inheritdoc}
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $this->configureSettings($resolver);
    }

    /**
     * Define the default options for the block.
     *
     * @param OptionsResolver $resolver
     */
    public function configureSettings(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascripts($media)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKeys(BlockInterface $block)
    {
        return array(
            'block_id' => $block->getId(),
            'updated_at' => $block->getUpdatedAt() ? $block->getUpdatedAt()->format('U') : strtotime('now'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse($blockContext->getTemplate(), array(
            'block_context' => $blockContext,
            'block' => $blockContext->getBlock(),
        ), $response);
    }
}
