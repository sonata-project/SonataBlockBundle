<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Templating\EngineInterface;
use Sonata\AdminBundle\Form\FormMapper;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;

use Sonata\AdminBundle\Validator\ErrorElement;

/**
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ActionBlockService extends BaseBlockService
{
    private $kernel;

    /**
     * @param $name
     * @param \Symfony\Component\Templating\EngineInterface $templating
     * @param \Symfony\Component\HttpKernel\HttpKernelInterface $kernel
     */
    public function __construct($name, EngineInterface $templating, HttpKernelInterface $kernel)
    {
        parent::__construct($name, $templating);

        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockInterface $block, Response $response = null)
    {
        $parameters = (array)json_decode($block->getSetting('parameters'), true);
        $parameters = array_merge($parameters, array('_block' => $block));

        $settings = array_merge($this->getDefaultSettings(), (array)$block->getSettings());
        try {
            $actionContent = $this->kernel->render($settings['action'], $parameters);
        } catch (\Exception $e) {
            throw $e;
        }

        $content = self::mustache($block->getSetting('layout'), array(
            'CONTENT' => $actionContent
        ));

        return $this->renderResponse('SonataBlockBundle:Block:block_core_action.html.twig', array(
            'content'   => $content,
            'block'     => $block,
        ), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('layout', 'textarea', array()),
                array('action', 'text', array()),
                array('parameters', 'text', array()),
            )
        ));
    }

    /**
     * @static
     * @param $string
     * @param array $parameters
     * @return mixed
     */
    static public function mustache($string, array $parameters)
    {
        $replacer = function ($match) use ($parameters) {
            return isset($parameters[$match[1]]) ? $parameters[$match[1]] : $match[0];
        };

        return preg_replace_callback('/{{\s*(.+?)\s*}}/', $replacer, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Action (core)';
    }

    /**
     * {@inheritdoc}
     */
    function getDefaultSettings()
    {
        return array(
            'layout'      => '{{ CONTENT }}',
            'action'      => 'SonataBlockBundle:Block:empty',
            'parameters'  => '{}'
        );
    }
}