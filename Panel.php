<?php

/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2010 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */

namespace Nella\Doctrine;

use Nette\Debug,
	Nette\String;

/**
 * Doctrine SQL logger for Nette\Debug panel
 *
 * @author	Patrik Votoček
 * @package	Nella\Doctrine
 */
class Panel extends \Nette\Object implements \Nette\IDebugPanel, \Doctrine\DBAL\Logging\SQLLogger
{
	/** @var sting */
	protected $service;
	/** @var array */
	protected $data = array();
	/** @var int */
	private $i = 0;

	/**
	 * @param sting service name
	 */
	public function __construct($service = 'Doctrine\ORM\EntityManager')
	{
		$this->service = $service;
	}

	/**
	 * @return sting
	 */
	public function getId()
	{
		return 'doctrine';
	}

	/**
	 * @return string
	 */
	public function getPanel()
	{
		if (count($this->data) == 0) {
			return NULL;
		}

		$platform = get_class(\Nette\Environment::getApplication()->context->getService($this->service)->getConnection()->getDatabasePlatform());
		$platform = substr($platform, strrpos($platform, "\\") + 1, strrpos($platform, "Platform") - (strrpos($platform, "\\") + 1));
		$data = $this->data;
		$time = number_format(array_sum(array_map(function ($x) { return $x->time; }, $this->data)), 3, '.', ' ');
		$service = $this->service;
		$i = 0;

		/**
		 * @author		David Grudl
		 * @see			http://dibiphp.com
		 *
		 * @param string
		 */
		$dump = function ($sql) {
			$keywords1 = 'CREATE\s+TABLE|CREATE(?:\s+UNIQUE)?\s+INDEX|SELECT|UPDATE|INSERT(?:\s+INTO)?|REPLACE(?:\s+INTO)?|DELETE|FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|LIMIT|SET|VALUES|LEFT\s+JOIN|INNER\s+JOIN|TRUNCATE';
			$keywords2 = 'ALL|DISTINCT|DISTINCTROW|AS|USING|ON|AND|OR|IN|IS|NOT|NULL|LIKE|TRUE|FALSE|INTEGER|CLOB|VARCHAR|DATETIME|TIME|DATE|INT|SMALLINT|BIGINT|BOOL|BOOLEAN|DECIMAL|FLOAT|TEXT|VARCHAR|DEFAULT|AUTOINCREMENT|PRIMARY\s+KEY';

			// insert new lines
			$sql = " $sql ";
			$sql = String::replace($sql, "#(?<=[\\s,(])($keywords1)(?=[\\s,)])#", "\n\$1");
			if (strpos($sql, "CREATE TABLE") !== FALSE) {
				$sql = String::replace($sql, "#,\s+#i", ", \n");
			}

			// reduce spaces
			$sql = String::replace($sql, '#[ \t]{2,}#', " ");

			$sql = wordwrap($sql, 100);
			$sql = htmlSpecialChars($sql);
			$sql = String::replace($sql, "#([ \t]*\r?\n){2,}#", "\n");
			$sql = String::replace($sql, "#VARCHAR\\(#", "VARCHAR (");

			// syntax highlight
			$sql = String::replace($sql,
				"#(/\\*.+?\\*/)|(\\*\\*.+?\\*\\*)|(?<=[\\s,(])($keywords1)(?=[\\s,)])|(?<=[\\s,(=])($keywords2)(?=[\\s,)=])#s",
				function ($matches) {
					if (!empty($matches[1])) { // comment
						return '<em style="color:gray">'.$matches[1].'</em>';
					}
					if (!empty($matches[2])) { // error
						return '<strong style="color:red">'.$matches[2].'</strong>';
					}
					if (!empty($matches[3])) { // most important keywords
						return '<strong style="color:blue">'.$matches[3].'</strong>';
					}
					if (!empty($matches[4])) { // other keywords
						return '<strong style="color:green">'.$matches[4].'</strong>';
					}
					return NULL;
				}
			);

			$sql = trim($sql);
			return '<pre class="dump">'.$sql."</pre>\n";
		};

		ob_start();
		Debug::tryError();
		require_once __DIR__ . "/Panel.phtml";
		if (Debug::catchError($message)) {
			Debug::log($message);
			ob_get_clean();
			return NULL;
		} else {
			return ob_get_clean();
		}
	}

	/**
	 * @return string
	 */
	public function getTab()
	{
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC">'.count($this->data).' queries';
	}

	/**
	 * @param string
	 * @param array
	 * @param array
	 */
	public function startQuery($sql, array $params = null, array $types = null)
	{
		$this->i++;
		$this->data[$this->i] = (object) array('sql' => $sql, 'params' => $params, 'types' => $types, 'time' => 0);
		Debug::timer($this->getId() . '-watch-' . $this->i);
	}

	public function stopQuery()
	{
		$this->data[$this->i]->time = number_format(Debug::timer($this->getId() . '-watch-' . $this->i), 3, '.', ' ');
	}

	/**
	 * @param string
	 * @return Panel
	 */
	public static function createLogger($service = 'Doctrine\ORM\EntityManager')
	{
		return new static($service);
	}

	/**
	 * @param string
	 * @return Panel
	 */
	public static function createAndRegister($service = 'Doctrine\ORM\EntityManager')
	{
		$logger = static::createLogger($service);
		Debug::addPanel($logger);
		return $logger;
	}
}
