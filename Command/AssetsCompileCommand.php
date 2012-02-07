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
use Hatimeria\ExtJSBundle\Controller\JavascriptController;
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
        $files = $container->getParameter('hatimeria_ext_js.compiled_files');
        $domains = $container->getParameter("hatimeria_ext_js.translation_domains");
        $vendorPath = $container->getParameter("hatimeria_ext_js.javascript_vendor_path");
        
        $hdefault = new JavascriptController();
        $hdefault->setContainer($container);
        $dynamic = $hdefault->dynamicAction()->getContent();
        
        $hdirect = new DirectController();
        $hdirect->setContainer($container);
        $api = $hdirect->getApiAction()->getContent();
        
        $expose = $container->get('bazinga.exposetranslation.controller');
        $routing = $container->get('fos_js_routing.controller');
        $compressor = new JsCompressorFilter('vendor/bundles/Hatimeria/ExtJSBundle/Resources/jar/yuicompressor.jar');
        
        $extjs = "web/bundles/hatimeriaextjs/js/extjs/";
        
        // @todo recursive glob
        foreach($languages as $lang) {
            $ac = new AssetCollection;
            $ac->add(new FileAsset("web/".$vendorPath."/ext-all.js"));
            $ac->add(new FileAsset($extjs."/core/overrides.js"));
            $ac->add(new StringAsset($dynamic));
            $ac->add(new FileAsset($extjs."/translation/Translation.js"));
            foreach($domains as $domain) {
                $ac->add(new StringAsset($expose->exposeTranslationAction($domain, $lang, 'js')->getContent()));
            }
            $ac->add(new FileAsset("web/".$vendorPath."locale/ext-lang-".$lang.".js"));
            $ac->add(new FileAsset($extjs."/core/direct-api-handler.js"));
            $ac->add(new FileAsset($extjs."/routing/Routing.js"));
            $ac->add(new GlobAsset($extjs."/core/utils/*"));
            $ac->add(new GlobAsset($extjs."/core/mixins/*"));
            $ac->add(new GlobAsset($extjs."/core/store/*"));
            $ac->add(new GlobAsset($extjs."/core/model/*"));
            $ac->add(new GlobAsset($extjs."/core/grid/*"));
            $ac->add(new GlobAsset($extjs."/core/response/*"));
            $ac->add(new GlobAsset($extjs."/core/window/*"));
            $ac->add(new FileAsset($extjs."/core/form/BaseForm.js"));
            $ac->add(new GlobAsset($extjs."/core/form/*"));
            $ac->add(new GlobAsset($extjs."/core/field/*"));
            $ac->add(new GlobAsset($extjs."/core/user/*"));
            $ac->add(new GlobAsset($extjs."/treeselect/store/*"));
            $ac->add(new GlobAsset($extjs."/treeselect/panel/*"));
            $ac->add(new GlobAsset($extjs."/treeselect/field/*"));
            $ac->add(new StringAsset($api));
            $ac->add(new StringAsset($routing->indexAction('js')->getContent()));
            foreach($files as $file) {
                $ac->add(new FileAsset('web/' . $file));
            }

            $ac->ensureFilter($compressor);
            
            $compiled = $ac->dump();
            @mkdir('web/compiled/js', 0755, true);
            file_put_contents(sprintf("web/compiled/js/ext-%s.js", $lang), $compiled);  
        }

        $output->writeln('Assets compiled');
    }
}
