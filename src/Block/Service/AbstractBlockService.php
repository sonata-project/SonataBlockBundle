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
     */
    public function __construct($twigOrDeprecatedName = null, ?Environment $twig = null)
    {
        if (!$twigOrDeprecatedName instanceof Environment && 0 !== strpos(static::class, __NAMESPACE__.'\\')) {
            $class = 'c' === static::class[0] && 0 === strpos(static::class, "class@anonymous\0") ? get_parent_class(static::class).'@anonymous' : static::class;

            @trigger_error(
                sprintf(
                    'Passing %s as argument 1 to %s::%s() is deprecated since sonata-project/block-bundle 3.16 and will throw a \TypeError as of 4.0. You must pass an instance of %s instead',
                    \gettype($twigOrDeprecatedName),
                    $class,
                    __FUNCTION__,
                    Environment::class
                ),
                E_USER_DEPRECATED
            );
        }

        if ($twigOrDeprecatedName instanceof Environment) {
            $this->name = '';
            $this->twig = $twigOrDeprecatedName;
        } elseif (\is_string($twigOrDeprecatedName)) {
            $this->name = $twigOrDeprecatedName;
            $this->twig = $twig;
        } else {
            $class = 'c' === static::class[0] && 0 === strpos(static::class, "class@anonymous\0") ? get_parent_class(static::class).'@anonymous' : static::class;

            throw new \TypeError(sprintf(
                'Argument 1 passed to %s::%s() must be a string or an instance of %s, %s given.',
                $class,
                __FUNCTION__,
                Environment::class,
                \is_object($twigOrDeprecatedName) ? 'instance of '.\get_class($twigOrDeprecatedName) : \gettype($twigOrDeprecatedName)
            ));
        }
    }

    /**
     * Returns a Response object than can be cacheable.
     */
    public function renderResponse(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $response = $response ?? new Response();

        $response->setContent($this->twig->render($view, $parameters));

        return $response;
    }

    /**
     * Returns a Response object that cannot be cacheable, this must be used if the Response is related to the user.
     * A good solution to make the page cacheable is to configure the block to be cached with javascript ...
     */
    public function renderPrivateResponse(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return $this->renderResponse($view, $parameters, $response)
            ->setTtl(0)
            ->setPrivate()
        ;
    }

    public function setDefaultSettings(OptionsResolverInterface $resolver): void
    {
        if (!$resolver instanceof OptionsResolver) {
            throw new \BadMethodCallException(
                sprintf('Calling %s with %s is unsupported', __METHOD__, \get_class($resolver))
            );
        }

        $this->configureSettings($resolver);
    }

    /**
     * Define the default options for the block.
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
    }

    public function getCacheKeys(BlockInterface $block): array
    {
        return [
            'block_id' => $block->getId(),
            'updated_at' => $block->getUpdatedAt() ? $block->getUpdatedAt()->format('U') : strtotime('now'),
        ];
    }

    public function load(BlockInterface $block): void
    {
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        return $this->renderResponse($blockContext->getTemplate(), [
            'block_context' => $blockContext,
            'block' => $blockContext->getBlock(),
        ], $response);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
