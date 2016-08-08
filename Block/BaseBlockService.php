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

use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;

@trigger_error(
    'This class is deprecated since 3.x and will be removed with the 4.0 release.'.
    'Use '.__NAMESPACE__.'\Block\Service\AbstractBlockService instead.',
    E_USER_DEPRECATED
);

/**
 * BaseBlockService.
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @deprecated since 3.x, to be removed with 4.0
 */
abstract class BaseBlockService extends AbstractAdminBlockService implements BlockAdminServiceInterface
{
}
