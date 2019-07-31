<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block\Service;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
abstract class AbstractBlockService implements BlockServiceInterface
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var EngineInterface|null
     */
    protected $templating;

    /**
     * @param EngineInterface|string $templatingOrDeprecatedName
     */
    public function __construct($templatingOrDeprecatedName = null, EngineInterface $templating = null)
    {
        if (!$templatingOrDeprecatedName instanceof EngineInterface && 0 !== strpos(static::class, __NAMESPACE__.'\\')) {
            @trigger_error(
                sprintf(
                    'Passing %s as argument 1 to %s::%s() is deprecated since sonata-project/block-bundle 3.16 and will throw a \TypeError as of 4.0. You must pass an instance of %s instead',
                    \gettype($templatingOrDeprecatedName),
                    static::class, __FUNCTION__,
                    EngineInterface::class
                ),
                E_USER_DEPRECATED
            );
        }

        if ($templatingOrDeprecatedName instanceof EngineInterface) {
            $this->name = '';
            $this->templating = $templatingOrDeprecatedName;
        } else {
            $this->name = $templatingOrDeprecatedName;
            $this->templating = $templating;
        }
    }

    /**
     * Returns a Response object than can be cacheable.
     *
     * @param string $view
     *
     * @return Response
     */
    public function renderResponse($view, array $parameters = [], Response $response = null)
    {
        return $this->getTemplating()->renderResponse($view, $parameters, $response);
    }

    /**
     * Returns a Response object that cannot be cacheable, this must be used if the Response is related to the user.
     * A good solution to make the page cacheable is to configure the block to be cached with javascript ...
     *
     * @param string $view
     *
     * @return Response
     */
    public function renderPrivateResponse($view, array $parameters = [], Response $response = null)
    {
        return $this->renderResponse($view, $parameters, $response)
            ->setTtl(0)
            ->setPrivate()
        ;
    }

    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $this->configureSettings($resolver);
    }

    /**
     * Define the default options for the block.
     */
    public function configureSettings(OptionsResolver $resolver)
    {
    }

    public function getCacheKeys(BlockInterface $block)
    {
        return [
            'block_id' => $block->getId(),
            'updated_at' => $block->getUpdatedAt() ? $block->getUpdatedAt()->format('U') : strtotime('now'),
        ];
    }

    public function load(BlockInterface $block)
    {
    }

    public function getJavascripts($media)
    {
        return [];
    }

    public function getStylesheets($media)
    {
        return [];
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse($blockContext->getTemplate(), [
            'block_context' => $blockContext,
            'block' => $blockContext->getBlock(),
        ], $response);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTemplating()
    {
        return $this->templating;
    }
}
