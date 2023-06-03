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

    use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;

    class CustomBlockServiceTest extends AbstractBlockServiceTestCase
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

        public function testExecute(): void
        {
            $blockService = new CustomBlockService('foo', $this->twig);
            $blockContext = $this->getBlockContext($blockService);

            $blockService->execute($blockContext);

            $this->assertSame($blockContext, $this->templating->parameters['context']);
            $this->assertInternalType('array', $this->templating->parameters['settings']);
            $this->assertInstanceOf('Sonata\BlockBundle\Model\BlockInterface', $this->templating->parameters['block']);
            $this->assertSame('bar', $this->templating->parameters['foo']);
        }
    }
