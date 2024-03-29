<?php

namespace UPS\VCGBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Formatter\OutputFormatterStyle;

//use Symfony\Component\DomCrawler\Crawler;
//use Symfony\Component\HttpKernel\Client;
//use Symfony\Component\HttpKernel\HttpKernelInterface;
//use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

//use Symfony\Component\Process\Process;

class SiteExportCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Array
     */
    private $urls = array(
        '/transition-guide/dd-form-214-guide.html',
        '/index.html',
        '/article/brand-intro-home.html',
        '/video/thank-you-from-ups.html',
        '/video/military-ready.html',
        '/jobs/video/feeder-driver.html',
        '/jobs/video/frontline-supervisor.html',
        '/jobs/video/hr.html',
        '/jobs/video/cover-driver.html',
        '/jobs/video/mechanic.html',
        '/jobs/video/otr-driver.html',
        '/jobs/video/package-driver.html',
        '/jobs/video/sorter.html',
        '/jobs/video/technician.html',
        '/transition-guide/video/resume.html',
        '/transition-guide/video/networking.html',
        '/transition-guide/video/social-media.html',
        '/transition-guide/video/interview.html',
        '/culture-benefits/video/feeder-driver.html',
        '/culture-benefits/video/frontline-supervisor.html',
        '/culture-benefits/video/hr.html',
        '/culture-benefits/video/cover-driver.html',
        '/culture-benefits/video/mechanic.html',
        '/culture-benefits/video/otr-driver.html',
        '/culture-benefits/video/package-driver.html',
        '/culture-benefits/video/sorter.html',
        '/culture-benefits/video/technician.html',
        '/transition-guide.html',
        '/transition-guide/dd-form-214-guide.html',
        '/transition-guide/article/employment-resources.html',
        '/transition-guide/article/brand-intro.html',
        '/transition-guide/article/cover-letter-resume.html',
        '/transition-guide/article/marketing-yourself.html',
        '/transition-guide/article/networking-essentials.html',
        '/transition-guide/article/recruiters.html',
        '/transition-guide/article/career-fair.html',
        '/transition-guide/article/interview.html',
        '/events.html',
        '/job-map.html',
        '/jobs.html',
        '/military-skills-translator.html',
        '/culture-benefits.html',
        '/culture-benefits/partners.html',
        '/culture-benefits/history.html',
        '/culture-benefits/article/faq.html',
        '/culture-benefits/article/values.html',
        '/culture-benefits/article/family-programs.html',
        '/culture-benefits/article/benefits.html',
        '/culture-benefits/article/student-programs.html',
        '/culture-benefits/article/esgr.html',
        '/culture-benefits/article/ra-intro.html',
        '/culture-benefits/article/ra-application.html',
        '/culture-benefits/article/ra-responsibilities.html',
        '/culture-benefits/article/ra-gi-benefits.html',
        '/culture-benefits/article/ra-post-911-payments.html',
        '/culture-benefits/article/ra-mgib-payments.html',
        '/culture-benefits/article/ra-glossary.html',
        '/culture-benefits/article/ra-faq.html',
        '/culture-benefits/history/photo/1.html',
        '/culture-benefits/history/photo/2.html',
        '/culture-benefits/history/photo/3.html',
        '/culture-benefits/history/photo/4.html',
        '/culture-benefits/history/photo/5.html',
        '/culture-benefits/history/photo/6.html',
        '/culture-benefits/history/photo/7.html',
        '/culture-benefits/history/photo/8.html',
        '/culture-benefits/history/photo/9.html',
        '/culture-benefits/history/photo/10.html',
        '/culture-benefits/history/photo/11.html',
        '/culture-benefits/history/photo/12.html',
        '/culture-benefits/history/photo/13.html',
        '/culture-benefits/history/photo/14.html',
        '/culture-benefits/history/photo/15.html',
        '/culture-benefits/history/photo/16.html',
        '/culture-benefits/history/photo/17.html',
        '/culture-benefits/history/photo/18.html',
        '/culture-benefits/history/photo/19.html',
        '/culture-benefits/history/photo/20.html',
        '/culture-benefits/history/photo/21.html',
        '/culture-benefits/history/photo/22.html',
        '/culture-benefits/history/photo/23.html',
        '/culture-benefits/history/photo/24.html',
        '/culture-benefits/history/photo/25.html',
        '/culture-benefits/history/photo/26.html',
        '/culture-benefits/history/photo/27.html',
        '/404.html'
    );

    /**
     * Configure
     *
     * @author  Justin Maurer <justin@360zen.com
     */

    protected function configure() {
        $this
            ->setName('ups:crawl:export')
            ->setDescription('Export site to flat file setup')
            ->setHelp(<<<EOT
                The <info>ups:crawl:export</info> command crawls the UPS site with a fixed list of URLs.

                This will overwrite previously exported files.
EOT
            )
            ->addOption('dry-run', 'dry', null, 'Print a list of URLs to be exported, instead of writing files.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $kernel = $this->_createKernel();
        $client = $kernel->getContainer()->get( 'test.client' );
        $dryRun = $input->getOption('dry-run');
        $this->writeSite($client, $dryRun, $output);
        $output->writeln('<fg=green>Site export complete</fg=green>');
    }

    protected function _createKernel() {

        $rootDir = $this->getContainer()->get( 'kernel' )->getRootDir();
        require_once( $rootDir . '/AppKernel.php' );
        $kernel = new \AppKernel( 'test', true );
        $kernel->boot();

        return $kernel;
    }

    protected function dumpAssets(OutputInterface $output)
    {
        $command = $this->getApplication()->find('assetic:dump');

        $arguments = array(
            'command' => 'assetic:dump'
        );

        $input = new ArrayInput($arguments);
        $command->run($input, $output);

        $output->writeln('Dumping assets');
    }
    protected function writeSite($client, $dryRun, OutputInterface $output) {

        $fs = new Filesystem();

        $urls = $this->urls;
        foreach ( $urls as $url ) {
            //find last slash
            $pos = strrpos($url, '/' );

            //slug starts 1 position after last slash
            $slugStart = -(strlen($url) - $pos - 1);

            //parse slug
            $slug = substr($url, $slugStart);

            //parse folder path
            $folderPath = 'site-export' . substr($url, 0, $slugStart);

            if ($dryRun == true) {
                if ( $fs->exists($folderPath) ) {
                    $output->writeln('Folder path ' . $folderPath . ' exists.' );
                } else {
                    $output->writeln('Folder path ' . $folderPath . ' will be created.' );
                }
                try {
                    $client->request( 'GET', $url );
                    $statusCode = $client->getResponse()->getStatusCode();
                    if ($statusCode != 200) {
                        throw new \Exception("Page Does Not Exist", 3489);
                    }

                    $output->writeln('<fg=green>'.$slug . ' will be written to ' . $folderPath.$slug.'</fg=green>' );
                } catch (\Exception $ex) {
                    $output->writeln('<fg=red>'.$statusCode.' Nothing found at ' . $url .'</fg=red>' );
                }
            } else {
                try {
                    //create appropriate folder in ROOT/site-export/
                    $fs->mkdir($folderPath);
                } catch (IOExceptionInterface $e) {
                    //something went wrong
                    echo "An error occurred while creating your directory at ".$e->getPath();
                }
                $crawler = $client->request( 'GET', $url );
                $statusCode = $client->getResponse()->getStatusCode();
                    $fs->dumpFile($folderPath . $slug, "<!DOCTYPE html>\n<html>\n" . $crawler->html() . "\n</html>");
                    $output->writeln($slug . ' has been written to ' . $folderPath . $slug);

                    $output->writeln('assets have been copied to /site-export/bundles');
            }

        }

        if($dryRun != true){
            $this->dumpAssets($output);
            $fs->copy('web/favicon.ico','site-export/favicon.ico');
            $fs->copy('web/robots.txt','site-export/robots.txt');
            $fs->mirror('web/css', 'site-export/css');
            $fs->mirror('web/js', 'site-export/js');
            $fs->mirror('web/bundles', 'site-export/bundles');
        }
    }
}