.. index::
    double: Test Widgets; Definition

Testing
=======

Test Blocks
~~~~~~~~~~~

Given the following block service::

    class CustomBlockService extends AbstractBlockService
    {
        public function execute(BlockContextInterface $blockContext, Response $response = null): Response
        {
            return $this->renderResponse($blockContext->getTemplate(), [
                'context' => $blockContext,
                'block' => $blockContext->getBlock(),
                'settings' => $blockContext->getSettings(),
            ], $response);
        }

        public function configureSettings(OptionsResolver $resolver): array
        {
            $resolver->setDefaults([
                'foo' => 'bar',
                'attr' => [],
                'template' => false,
            ]);
        }
    }

You can write unit tests for block services with the following code::

    use Sonata\BlockBundle\Test\BlockServiceTestCase;

    class CustomBlockServiceTest extends BlockServiceTestCase
    {
        public function testDefaultSettings(): void
        {
            $blockService = new CustomBlockService('foo', $this->twig);
            $blockContext = $this->getBlockContext($blockService);

            $this->assertSettings([
                'foo' => 'bar',
                'attr' => [],
                'template' => false,
            ], $blockContext);
        }
    }
