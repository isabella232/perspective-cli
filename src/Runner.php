<?php
/**
 * Perspective CLI Runner Class.
 *
 * @package    Perspective
 * @subpackage Simulator
 * @author     Squiz Pty Ltd <products@squiz.net>
 * @copyright  2018 Squiz Pty Ltd (ABN 77 084 670 600)
 */

namespace PerspectiveCLI;

/**
 * Runner class
 */
class Runner
{

    /**
     * The CLI Application object.
     *
     * @var object
     */
    private $app = null;


    /**
     * Construct function for CLI runner.
     *
     * @param boolean $canInit Flag to register the initi command or not.
     *
     * @return void
     */
    final public function __construct(bool $canInit)
    {
        // Create a new CLI application.
        $this->app = new \Symfony\Component\Console\Application('Perspective CLI Runner', '1.0.0');

        // Add a project flag.
        $this->app->getDefinition()->addOptions([
            new \Symfony\Component\Console\Input\InputOption(
                'project',
                'p',
                \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                'The project to run the action on',
                null
            )
        ]);

        if ($canInit === true) {
            // Register the CLI Runner specific commands.
            $this->app->add(new \PerspectiveCLI\Command\InitCommand());
        }

    }//end __construct()


    /**
     * [execute description]
     *
     * @param array $commands  Array of commands to be registered.
     *
     * @return void
     */
    final public function registerCommands(array $commands)
    {
        if ($this->app === null) {
            throw new \Expection('CLI Runner not initialised.');
        }

        foreach ($commands as $class) {
            $this->app->add(new $class);
        }

    }//end registerCommands()


    /**
     * Runs the app.
     *
     * @return void
     */
    final public function run()
    {
        if ($this->app === null) {
            throw new \Expection('CLI Runner not initialised.');
        }

        $this->app->run();

    }//end run()


}//end class
