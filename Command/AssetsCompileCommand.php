<?php

namespace Hatimeria\ExtJsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\StringAsset;
use Hatimeria\ExtJSBundle\Controller\DefaultController;
use Hatimeria\ExtJSBundle\Controller\DirectController;
use Bazinga\ExposeTranslationBundle\Controller\Controller as ExposeTranslationController;
use Assetic\Filter\Yui\JsCompressorFilter;

/**
 * Compiles javascripts into use in headers.html.twig into one file
 * This file is placed under web/compiled/js directory
 * If compile config is set to true compiled file is used
 * 
 * @author Michał Wujas
 */
class AssetsCompileCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Creates compiled version of all needed javascripts')
            ->setHelp(<<<EOT
You must configure your prod environment to use generate file instead of multiple javascript resources
EOT
            )
            ->setName('hatimeria:extjs:compile')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $filesystem = $container->get('filesystem');
        $languages = $container->getParameter('hatimeria_ext_js.locales');
        
        $hdefault = new DefaultController();
        $hdefault->setContainer($container);
        $dynamic = $hdefault->dynamicAction()->getContent();
        
        $hdirect = new DirectController();
        $hdirect->setContainer($container);
        $api = $hdirect->getApiAction()->getContent();
        
        $expose = $container->get('bazinga.exposetranslation.controller');
        $routing = $container->get('fos_js_routing.controller');
        $compressor = new JsCompressorFilter('vendor/bundles/Hatimeria/ExtJSBundle/Resources/jar/yuicompressor.jar');
        
        // @todo recursive glob
        foreach($languages as $lang) {
            $ac = new AssetCollection;
            $ac->add(new FileAsset("web/bundles/hatimeriaextjs/js/extjs/vendor/extjs-4.0.7/ext-all.js"));
            $ac->add(new FileAsset("web/bundles/hatimeriaextjs/js/extjs/core/overrides.js"));
            $ac->add(new StringAsset($dynamic));
            $ac->add(new FileAsset("web/bundles/hatimeriaextjs/js/extjs/translation/Translation.js"));
            $ac->add(new StringAsset($expose->exposeTranslationAction('HatimeriaExtJSBundle', $lang, 'js')->getContent()));
            $ac->add(new StringAsset($expose->exposeTranslationAction('validators', $lang, 'js')->getContent()));
            $ac->add(new StringAsset($expose->exposeTranslationAction('messages', $lang, 'js')->getContent()));
            $ac->add(new StringAsset($expose->exposeTranslationAction('HatimeriaAdminBundle', $lang, 'js')->getContent()));
            $ac->add(new FileAsset("web/bundles/hatimeriaextjs/js/extjs/vendor/extjs-4.0.7/locale/ext-lang-".$lang.".js"));
            $ac->add(new FileAsset("web/bundles/hatimeriaextjs/js/extjs/routing/Routing.js"));
            $ac->add(new GlobAsset("web/bundles/hatimeriaextjs/js/extjs/core/store/*"));
            $ac->add(new GlobAsset("web/bundles/hatimeriaextjs/js/extjs/core/model/*"));
            $ac->add(new GlobAsset("web/bundles/hatimeriaextjs/js/extjs/core/grid/*"));
            $ac->add(new GlobAsset("web/bundles/hatimeriaextjs/js/extjs/core/response/*"));
            $ac->add(new GlobAsset("web/bundles/hatimeriaextjs/js/extjs/core/window/*"));
            $ac->add(new GlobAsset("web/bundles/hatimeriaextjs/js/extjs/core/form/*"));
            $ac->add(new StringAsset($api));
            $ac->add(new StringAsset($routing->indexAction('js')->getContent()));
            $ac->ensureFilter($compressor);
            
            $compiled = $ac->dump();
            @mkdir('web/compiled/js', 0755, true);
            file_put_contents(sprintf("web/compiled/js/ext-%s.js", $lang), $compiled);  
        }

        $output->writeln('Assets compiled');
    }
}
