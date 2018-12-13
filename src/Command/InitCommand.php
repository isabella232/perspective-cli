<?php

namespace PerspectiveCLI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class InitCommand extends Command
{

    protected static $defaultName = 'init';


    /**
     * Configures the init command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Creates a blank repo with the simulator installed.');
        $this->setHelp('This command creates a blank repo with the given directory name.');
        $this->addArgument('name', InputArgument::REQUIRED, 'System Name');
        $this->addArgument('repo-url', InputArgument::OPTIONAL, '');

    }//end configure()


    /**
     * Make sure that the system name is set.
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (empty($input->getArgument('name')) === true) {
            $helper     = $this->getHelper('question');
            $question   = new \Symfony\Component\Console\Question\Question('Please enter a System name: ');
            $bundleName = $helper->ask($input, $output, $question);
            $input->setArgument('name', $bundleName);
        }

    }//end interact()


    /**
     * Executes the create new project command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Project Creator',
            '================================================',
            '',
        ]);

        $systemName = str_replace(' ', '_', $input->getArgument('name'));
        $systemName = str_replace('-', '_', $systemName);
        $parts     = explode('_', $systemName);
        if (count($parts) === 1) {
            if (strtoupper($parts[0]) === $parts[0]) {
                $parts[0] = ucfirst(strtolower($parts[0]));
            }
        } else {
            foreach ($parts as $idx => $part) {
                $parts[$idx] = ucfirst(strtolower($part));
            }
        }

        $systemName = implode('', $parts);
        $systemDir  = getcwd().'/'.$systemName;
        if (mkdir($systemDir) === false) {
            exit(sprintf('Unable to create system directory "%s"', $systemDir));
        }

        if (mkdir($systemDir.'/projects') === false) {
            exit(sprintf('Unable to create projects directory "%s"', $systemDir.'/projects'));
        }

        $gitignore = $systemDir.'/.gitignore';
        file_put_contents($gitignore, '/simulator/
    /vendor/
    composer.lock');

        $composer = $systemDir.'/composer.json';
        file_put_contents(
            $composer,
            json_encode(
                [
                    'name'         => $systemName,
                    'description'  => $systemName,
                    'repositories' => [
                        [
                            'type'    => 'path',
                            'url'     => '../PerspectiveSimulator',
                            'options' => [
                                'symlink' => false,
                            ],
                        ],
                    ],
                    'require'      => [
                        'Perspective/Simulator' => '@dev',
                    ],
                ],
                128
            )
        );

        $phpunit = $systemDir.'/phpunit.xml.dist';
        file_put_contents($phpunit, '<phpunit bootstrap="vendor/autoload.php" stderr="true">
        <testsuites>
            <testsuite name="'.$systemName.'">
                <directory>projects/*/tests</directory>
            </testsuite>
        </testsuites>
    </phpunit>');

        $systemInfo = $systemDir.'/system_info.json';
        file_put_contents(
            $systemInfo,
            json_encode(
                [
                    'name'      => $systemName,
                    'tag'       => 'Development',
                    'colour'    => 'red',
                    'systemURL' => '',
                    'showTag'   => true,
                ],
                128
            )
        );

        exec('git -C '.$systemDir.' init');
        if (empty($input->getArgument('repo-url')) === false) {
            // Repo url set so lets initialise it.
            exec('git -C '.$systemDir. ' remote add origin '.$opts['repo-url']);
        }

        chdir($systemDir);
        exec('composer install');
        if (is_dir('./vendor/Perspective/Simulator') === true) {
            exec('php ./vendor/Perspective/Simulator/src/CLI/bin/perspective.php -i');
        }

        $output->writeln([
            '================================================',
            '',
            'New project created at '.getcwd(),
            '',
        ]);

    }//end execute()


}//end class