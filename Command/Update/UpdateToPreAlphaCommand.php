<?php
/*
 * This file is part of the AlphaLemon CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) AlphaLemon <webmaster@alphalemon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.alphalemon.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace AlphaLemon\AlphaLemonCmsBundle\Command\Update;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use AlphaLemon\AlphaLemonCmsBundle\Core\Repository\Propel\Base\AlPropelOrm;

/**
 * Upgrades to pre alpha
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class UpdateToPreAlphaCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Updates the database to AlphaLemon CMS Pre Alpha')
            ->setDefinition(array(
                new InputArgument('dsn', InputArgument::REQUIRED, 'The dsn to connect the database'),
                new InputOption('user', '', InputOption::VALUE_OPTIONAL, 'The database user', 'root'),
                new InputOption('password', null, InputOption::VALUE_OPTIONAL, 'The database password', ''),
                new InputOption('driver', null, InputOption::VALUE_OPTIONAL, 'The database driver', 'mysql'),
            ))
            ->setName('alphalemon:update-to-pre-alpha');
    }

    /**
     * @see Command
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = new \PropelPDO($input->getArgument('dsn'), $input->getOption('user'), $input->getOption('password'));
        $orm = new AlPropelOrm($connection);
        
        $sqlFile = sprintf(__DIR__ . '/../../Resources/dbupdate/%s/AlphaLemonCmsPreAlpha.sql', $input->getOption('driver'));
        if (is_file($sqlFile)) {
            $updateQueries = file_get_contents($sqlFile);

            $queries = explode(';', $updateQueries);
            foreach ($queries as $query) {
                $orm->executeQuery($query);
            }

            $output->writeln('<info>The database has been updated.</info>');
        } else {
            throw new \Exception(sprintf('The file %s has not been found. AlphaLemon provides only the mysql queries required to updated the database. To fix this, please create a %s folder under the %s and adjust the provided queries for your database, then launch the command again.', $sqlFile, $input->getOption('driver'), dirname($sqlFile)));
        }
    }
}