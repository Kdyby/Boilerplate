<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations\Tools;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\Doctrine\Schema\SchemaTool;
use Kdyby\Packages\Package;
use Nette;
use Symfony\Component\Console\Output;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DropPackageSchema extends Nette\Object
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $entityManager;

	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	private $outputWriter;

	/**
	 * @var \Kdyby\Packages\Package
	 */
	private $package;



	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \Kdyby\Packages\Package $package
	 */
	public function __construct(EntityManager $em, Package $package)
	{
		$this->entityManager = $em;
		$this->package = $package;
	}



	/**
	 * @param boolean $commit
	 *
	 * @throws \Exception
	 */
	public function create($commit = FALSE)
	{
		// metadata
		$metadata = PartialSchemaComparator::collectPackageMetadata(
			$this->entityManager,
			$this->package
		);

		$connection = $this->entityManager->getConnection();
		$connection->beginTransaction();

		$this->message("");
		$this->message("Migrating <comment>" . $this->package->getName() . "</comment>");
		$this->message("No migrations are available, will only drop schema.");

		try {
			$start = microtime(TRUE);

			$schemaTool = new SchemaTool($this->entityManager);
			foreach ($schemaTool->getDropSchemaSQL($metadata) as $query) {
				$this->message('<comment>-></comment> ' . $query);

				if ($commit) {
					$connection->executeQuery($query);
				}
			}

			if (isset($query)) {
				$time = number_format((microtime(TRUE) - $start) * 1000, 1, '.', ' ');
				$this->message('<info>--</info> tables dropped in ' . $time . ' ms');

			} else {
				$this->message('<info>SS</info> schema is already up to date');
			}

			$connection->commit();

		} catch (\Exception $e) {
			$this->message('<error>Dropping of tables for package ' . $this->package->getName() . ' failed. ' . $e->getMessage() . '</error>');

			$connection->rollback();
			throw $e;
		}
	}



	/**
	 * @param \Symfony\Component\Console\Output\OutputInterface $writer
	 */
	public function setOutputWriter(Output\OutputInterface $writer)
	{
		$this->outputWriter = $writer;
	}



	/**
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function getOutputWriter()
	{
		if ($this->outputWriter === NULL) {
			$this->outputWriter = new Output\ConsoleOutput();
		}

		return $this->outputWriter;
	}



	/**
	 * @param string $message
	 */
	protected function message($message)
	{
		if ($this->outputWriter) {
			$this->outputWriter->writeln('    ' . $message);
		}
	}

}
