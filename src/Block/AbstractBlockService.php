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

@trigger_error(
    'The '.__NAMESPACE__.'\AbstractBlockService class is deprecated since 3.2 '.
    'and will be removed with the 4.0 release.'.
    'Use '.__NAMESPACE__.'\Service\AbstractBlockService instead.',
    E_USER_DEPRECATED
);

/**
 * NEXT_MAJOR: remove this class.
 *
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 *
 * @deprecated since 3.2, to be removed with 4.0
 */
abstract class AbstractBlockService extends \Sonata\BlockBundle\Block\Service\AbstractBlockService
{
}
