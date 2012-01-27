<?php

namespace Hatimeria\ExtJsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Install symlink to vendor hatimeriaextjs sources
 * 
 * @author Michał Wujas
 */
class AssetsInstallCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption('docs', null, InputOption::VALUE_NONE, 'Generate docs in web directory - /compiled/js/docs')
            ->setDescription('Install bundles web assets under a public web directory')
            ->setHelp(<<<EOT
Adding extjs libraries to extjs bundle
EOT
            )
            ->setName('hatimeria:extjs:install')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!function_exists('symlink') && $input->getOption('symlink')) {
            throw new \InvalidArgumentException('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }

        $filesystem = $this->getContainer()->get('filesystem');

        $originDir = $this->getContainer()->getParameter('kernel.root_dir') . '/../vendor/hatimeria/HatimeriaExtJS/src';
        $dir = realpath($originDir);

        if (false === $dir) {
            $msg = sprintf("ExtJS source code wasn't found in %s\n", $originDir);
            $msg = $msg . <<<EOT
Add those lines to your deps file:

[HatimeriaExtJS]
    git=git://github.com/hatimeria/HatimeriaExtJS.git
    target=/hatimeria/HatimeriaExtJS

EOT;
            throw new \RuntimeException($msg);
        }
        $originDir = $dir;

        $bundle    = $this->getContainer()->get('kernel')->getBundle('HatimeriaExtJSBundle', true);
        $targetDir = $bundle->getPath() . '/Resources/public/js/extjs';

        $filesystem->remove($targetDir);

        if ($input->getOption('symlink')) {
            $filesystem->symlink($originDir, $targetDir);
        } else {
            $filesystem->mkdir($targetDir, 0777);
            $filesystem->mirror($originDir, $targetDir);
        }
        
        $output->writeln('ExtJS lib installed');
        
        if($input->getOption("docs")) {
            $originDir = realpath($originDir.'/../docs');
            $jsDir = $this->getContainer()->getParameter('kernel.root_dir').'/../web/compiled/js/';
            $filesystem->mkdir($targetDir, 0777);
            $filesystem->mkdir($jsDir, 0777);
            $targetDir = $jsDir.'docs';
            $output->writeln($targetDir.' => '.$originDir);
            $output->writeln('ExtJS docs installed go to http://yourhost/compiled/js/docs');
            $filesystem->symlink($originDir, $targetDir);
        }

    }
}
