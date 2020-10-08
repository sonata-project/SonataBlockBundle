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

namespace Sonata\BlockBundle\Block;

use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;

interface BlockServiceManagerInterface
{
    /**
     * @param BlockServiceInterface|string $service
     */
    public function add(string $name, $service, array $contexts = []): void;

    /**
     * Return the block service linked to the link.
     */
    public function get(BlockInterface $block): BlockServiceInterface;

    public function getServices(): array;

    public function getServicesByContext(string $context, bool $includeContainers = true): array;

    public function has(string $name): bool;

    public function getService(string $name): BlockServiceInterface;

    public function validate(ErrorElement $errorElement, BlockInterface $block): void;
}
