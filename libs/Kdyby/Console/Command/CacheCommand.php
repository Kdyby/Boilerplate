<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Console\Command;

use Doctrine\ORM\Mapping\MappingException;
use Kdyby;
use Nette;
use Nette\Caching\Cache;
use Nette\Utils\Finder;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputOption;



/**
 * Show information about mapped entities
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CacheCommand extends Console\Command\Command
{

	/**
	 */
	protected function configure()
	{
		$this
			->setName('kdyby:clear-cache')
			->setDescription('Clears cache')
			->addOption('namespace', 'ns', InputOption::VALUE_OPTIONAL, 'Namespace to invalidate')
			->addOption('tag', NULL, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Tags to invalidate')
			->setHelp("The <info>kdyby:clear-cache</info> can invalidate cache, it's namespace or by tag.");
	}



	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @throws \Kdyby\InvalidArgumentException
	 */
	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		if ($input->getOption('namespace') !== NULL && $input->getOption('tag') !== array()) {
			throw new Kdyby\InvalidArgumentException("Specify either tag or namespace, not both.");
		}

		$output->writeln('');
		if (($ns = $input->getOption('namespace')) !== NULL) {
			$this->clearNamespace($ns);
			$output->writeln('Cache namespace "' . $ns . '" has been invalidated.');

		} else {
			$tags = $input->getOption('tag') ?: NULL;
			foreach ($this->getStorages() as $storage) {
				$storage->clean($tags ? array(Cache::TAGS => $tags) : array(Cache::ALL => TRUE));
			}

			if (is_array($tags)) {
				$output->writeln('Cache tags "' . implode('", "', $tags) . '" were invalidated.');

			} else {
				$output->writeln('Cache has been invalidated.');
			}

		}
		$output->writeln('');
	}



	/**
	 * @param string $ns
	 * @return bool
	 */
	private function clearNamespace($ns)
	{
		foreach (Finder::find('*')->from($dir = $this->getNamespaceDir($ns))->childFirst() as $entry) {
			if ($entry->isDir()) {
				@rmdir($entry->getRealPath());
				continue;
			}
			@unlink($entry->getRealPath());
		}
		return @rmdir($dir);
	}



	/**
	 * Returns file name.
	 *
	 * @param string $namespace
	 *
	 * @return string
	 */
	private function getNamespaceDir($namespace)
	{
		$cacheDir = $this->getContainer()->expand('%tempDir%/cache');
		$dir = urlencode($namespace);
		if ($a = strrpos($dir, $sep = urlencode(Cache::NAMESPACE_SEPARATOR))) {
			$dir = substr_replace($dir, '/_', $a, strlen($sep));
		}
		return $cacheDir . '/_' . $dir;
	}



	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	private function getContainer()
	{
		return $this->getHelper('di')->getContainer();
	}



	/**
	 * @return \Nette\Caching\Storages\FileStorage[]
	 */
	private function getStorages()
	{
		return array(
			$this->getHelper('cacheStorage')->getStorage(),
			$this->getHelper('phpFileStorage')->getStorage(),
		);
	}

}
