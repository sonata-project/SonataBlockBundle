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
     * @param \Sonata\AdminBundle\Form\FormMapper      $form
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     *
     * @return void
     */
    function buildEditForm(FormMapper $form, BlockInterface $block);

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper      $form
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     *
     * @return void
     */
    function buildCreateForm(FormMapper $form, BlockInterface $block);

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface        $block
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     *
     * @return void
     */
    function execute(BlockInterface $block, Response $response = null);

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\BlockBundle\Model\BlockInterface   $block
     *
     * @return void
     */
    function validateBlock(ErrorElement $errorElement, BlockInterface $block);

    /**
     * @return string
     */
    function getName();

    /**
     * Returns the default settings link to the service
     *
     * @return array
     */
    function getDefaultSettings();

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     *
     * @return void
     */
    function load(BlockInterface $block);

    /**
     * @param string $media
     *
     * @return array
     */
    function getJavacripts($media);

    /**
     * @param string $media
     *
     * @return array
     */
    function getStylesheets($media);

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     *
     * @return array
     */
    function getCacheKeys(BlockInterface $block);
}