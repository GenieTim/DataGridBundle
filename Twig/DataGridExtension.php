<?php


namespace Pfilsx\DataGrid\Twig;


use Pfilsx\DataGrid\Grid\DataGrid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigFunction;
use Twig_Environment;
use Twig_Extension;

class DataGridExtension extends Twig_Extension
{
    const DEFAULT_TEMPLATE = '@DataGrid/grid.blocks.html.twig';
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('grid_view', [$this, 'generateGrid'], [
                'needs_environment' => true,
                'is_safe' => ['html']
            ]),
        ];
    }

    public function generateGrid(Twig_Environment $environment, DataGrid $grid, array $attributes = []){
        $template = $grid->getTemplate();
        if (!$template->hasBlock('grid_table', [])){
            $template = $environment->loadTemplate(self::DEFAULT_TEMPLATE);
            $grid->setTemplate($template);
        }
        return $template->renderBlock('grid_table', [
            'attr' => $attributes,
            'data_grid' => $grid,
            'request' => $this->container->get('request_stack')->getCurrentRequest()
        ]);
    }

}