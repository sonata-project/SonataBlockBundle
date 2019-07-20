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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Twig\Environment;

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
     * @var Environment
     */
    private $twig;

    /**
     * @param Environment|string $twigOrDeprecatedName
     * @param Environment        $twig
     */
    public function __construct($twigOrDeprecatedName = null, Environment $twig = null)
    {
        if (!$twigOrDeprecatedName instanceof Environment && 0 !== strpos(static::class, __NAMESPACE__.'\\')) {
            @trigger_error(
                sprintf(
                    'Passing %s as argument 1 to %s::%s() is deprecated since sonata-project/block-bundle 3.x and will throw a \TypeError as of 4.0. You must pass an instance of %s instead',
                    \gettype($twigOrDeprecatedName),
                    static::class, __FUNCTION__,
                    Environment::class
                ),
                E_USER_DEPRECATED
            );
        }

        if ($twigOrDeprecatedName instanceof Environment) {
            $this->name = '';
            $this->twig = $twigOrDeprecatedName;
        } else {
            $this->name = $twigOrDeprecatedName;
            $this->twig = $twig;
        }
    }

    /**
     * Returns a Response object than can be cacheable.
     *
     * @param string   $view
     * @param Response $response
     *
     * @return Response
     */
    public function renderResponse($view, array $parameters = [], Response $response = null)
    {
        $response = $response ?? new Response();

        $response->setContent($this->twig->render($view, $parameters));

        return $response;
    }

    /**
     * Returns a Response object that cannot be cacheable, this must be used if the Response is related to the user.
     * A good solution to make the page cacheable is to configure the block to be cached with javascript ...
     *
     * @param string   $view
     * @param Response $response
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

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver): void
    {
        $this->configureSettings($resolver);
    }

    /**
     * Define the default options for the block.
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKeys(BlockInterface $block)
    {
        return [
            'block_id' => $block->getId(),
            'updated_at' => $block->getUpdatedAt() ? $block->getUpdatedAt()->format('U') : strtotime('now'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse($blockContext->getTemplate(), [
            'block_context' => $blockContext,
            'block' => $blockContext->getBlock(),
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getTwig()
    {
        return $this->twig;
    }
}
