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

namespace Sonata\BlockBundle\Tests\Block\Service;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\Service\TemplateBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Model\Block;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;
use Twig\Environment;

final class TemplateBlockServiceTest extends TestCase
{
    private const TEMPLATES = [
        'Teaser' => '@App/Block/teaser.html.twig',
        'Banner' => '@App/Block/banner.html.twig',
    ];

    public function testEditFormHasFreeTemplateFieldByDefault(): void
    {
        $formMapper = $this->createMock(FormMapper::class);
        $formMapper->expects(static::once())->method('add')
            ->with('settings', ImmutableArrayType::class, static::callback(
                static fn (array $options): bool => null === $options['keys'][0][1]
            ));

        (new TemplateBlockService(static::createStub(Environment::class)))->configureEditForm($formMapper, new Block());
    }

    public function testEditFormOffersOnlyConfiguredTemplates(): void
    {
        $formMapper = $this->createMock(FormMapper::class);
        $formMapper->expects(static::once())->method('add')
            ->with('settings', ImmutableArrayType::class, static::callback(
                static fn (array $options): bool => 'template' === $options['keys'][0][0]
                    && ChoiceType::class === $options['keys'][0][1]
                    && self::TEMPLATES === $options['keys'][0][2]['choices']
            ));

        (new TemplateBlockService(static::createStub(Environment::class), self::TEMPLATES))->configureEditForm($formMapper, new Block());
    }

    public function testValidateAcceptsAnyTemplateByDefault(): void
    {
        static::assertSame(0, $this->countTemplateViolations(new TemplateBlockService(static::createStub(Environment::class)), '@Other/any.html.twig'));
    }

    public function testValidateRejectsTemplatesThatAreNotConfigured(): void
    {
        $service = new TemplateBlockService(static::createStub(Environment::class), self::TEMPLATES);

        static::assertSame(0, $this->countTemplateViolations($service, '@App/Block/banner.html.twig'));
        static::assertSame(1, $this->countTemplateViolations($service, '@Other/any.html.twig'));
    }

    private function countTemplateViolations(TemplateBlockService $service, string $template): int
    {
        $block = new Block();
        $block->setSettings(['template' => $template]);

        $violations = Validation::createValidator()->validate($block, new Callback(
            static function (Block $block, ExecutionContextInterface $context) use ($service): void {
                $service->validate(new ErrorElement($block, $context, null), $block);
            }
        ));

        $count = 0;
        foreach ($violations as $violation) {
            if ('settings[template]' === $violation->getPropertyPath()) {
                ++$count;
            }
        }

        return $count;
    }
}
