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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Validator\ErrorElement;

use Symfony\Component\HttpFoundation\Response;

interface BlockServiceInterface
{
    /**
     * @param FormMapper     $form
     * @param BlockInterface $block
     *
     * @return void
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block);

    /**
     * @param FormMapper     $form
     * @param BlockInterface $block
     *
     * @return void
     */
    public function buildCreateForm(FormMapper $form, BlockInterface $block);

    /**
     * @param BlockInterface $block
     * @param null|Response  $response
     *
     * @return Response
     */
    public function execute(BlockInterface $block, Response $response = null);

    /**
     * @param ErrorElement   $errorElement
     * @param BlockInterface $block
     *
     * @return void
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block);

    /**
     * @return string
     */
    public function getName();

    /**
     * Returns the default settings link to the service
     *
     * @return array
     */
    public function getDefaultSettings();

    /**
     * @param BlockInterface $block
     *
     * @return void
     */
    public function load(BlockInterface $block);

    /**
     * @param $media
     *
     * @return array
     */
    public function getJavascripts($media);

    /**
     * @param $media
     *
     * @return array
     */
    public function getStylesheets($media);

    /**
     * @param BlockInterface $block
     *
     * @return array
     */
    public function getCacheKeys(BlockInterface $block);
}
