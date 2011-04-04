<?php
/*
 * Copyright (c) 2010 Patrik Votoček <patrik@votocek.cz>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kdyby\Translator;

require_once __DIR__ . "/shortcuts.php";

use Nette\Environment;
use Nette\String;



/**
 * Gettext translator.
 * This solution is partitionaly based on Zend_Translate_Adapter_Gettext (c) Zend Technologies USA Inc. (http://www.zend.com), new BSD license
 *
 * @author     Roman Sklenář
 * @author	   Miroslav Smetana
 * @author	   Patrik Votoček <patrik@votocek.cz>
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nettephp.com/gettext-translator
 * @package    NetteTranslator\Gettext
 * @version    0.5
 */
class Gettext extends \Nette\Object implements IEditable
{
	const SESSION_NAMESPACE = "NetteTranslator-Gettext";
	const CACHE_ENABLE = TRUE;
	const CACHE_DISABLE = FALSE;

	/** @var array */
	protected $dirs = array();

	/** @var string */
	protected $lang = "en";

	/** @var array */
	private $metadata;

	/** @var array<string|array> */
	protected $dictionary = array();

	/** @var bool */
	private $loaded = FALSE;

	/** @var bool */
	public static $cache = self::CACHE_DISABLE;



	/**
	 * Constructor
	 *
	 * @param array $dirs
	 * @param string $lang
	 */
	public function __construct(array $dirs = NULL, $lang = NULL)
	{
		if (count($dirs) > 0)
			$this->dirs = $dirs;
		if (empty($dirs)) {
			$dir = Environment::getVariable('langDir');
			if (empty($dir))
				throw new \InvalidStateException("Languages dir must be defined");
			$this->dirs[] = $dir;
		}

		$this->lang = $lang;
		if (empty($lang))
			$this->lang = Environment::getVariable('lang');
		if (empty($this->lang))
			throw new \InvalidStateException("Languages must be defined");
	}



	/**
	 * Load data
	 */
	protected function loadDictonary()
	{
		if (!$this->loaded) {
			$cache = Environment::getCache(self::SESSION_NAMESPACE);
			if (self::$cache && isset($cache['dictionary-'.$this->lang]))
				$this->dictionary = $cache['dictionary-'.$this->lang];
			else {
				$files = array();
				foreach ($this->dirs as $dir) {
					if (file_exists($dir."/".$this->lang.".mo")) {
						$this->parseFile($dir."/".$this->lang.".mo");
						$file[] = $dir."/".$this->lang.".mo";
					}
				}

				if (self::$cache) {
					$cache->save('dictionary-'.$this->lang, $this->dictionary, array(
						'expire' => time() * 60 * 60 * 2,
						'files' => $files,
						'tags' => array('dictionary-'.$this->lang)
					));
				}
			}
			$this->loaded = TRUE;
		}
	}



	/**
	 * Parse dictionary file
	 *
	 * @param string $file file path
	 */
	protected function parseFile($file)
	{
		$f = @fopen($file, 'rb');
		if (@filesize($file) < 10)
			\InvalidArgumentException("'$file' is not a gettext file.");

		$endian = FALSE;
		$read = function($bytes) use ($f, $endian)
		{
			$data = fread($f, 4 * $bytes);
			return $endian === FALSE ? unpack('V'.$bytes, $data) : unpack('N'.$bytes, $data);
		};

		$input = $read(1);
		if (String::lower(substr(dechex($input[1]), -8)) == "950412de")
			$endian = FALSE;
		elseif (String::lower(substr(dechex($input[1]), -8)) == "de120495")
			$endian = TRUE;
		else
			throw new \InvalidArgumentException("'$file' is not a gettext file.");

		$input = $read(1);

		$input = $read(1);
		$total = $input[1];

		$input = $read(1);
		$originalOffset = $input[1];

		$input = $read(1);
		$translationOffset = $input[1];

		fseek($f, $originalOffset);
		$orignalTmp = $read(2 * $total);
		fseek($f, $translationOffset);
		$translationTmp = $read(2 * $total);

		for ($i = 0; $i < $total; ++$i) {
			if ($orignalTmp[$i * 2 + 1] != 0) {
				fseek($f, $orignalTmp[$i * 2 + 2]);
				$original = @fread($f, $orignalTmp[$i * 2 + 1]);
			} else
				$original = "";

			if ($translationTmp[$i * 2 + 1] != 0) {
				fseek($f, $translationTmp[$i * 2 + 2]);
				$translation = fread($f, $translationTmp[$i * 2 + 1]);
				if ($original === "") {
					$this->parseMetadata($translation);
					continue;
				}

				$original = explode(String::chr(0x00), $original);
				$translation = explode(String::chr(0x00), $translation);
				$this->dictionary[is_array($original) ? $original[0] : $original]['original'] = $original;
				$this->dictionary[is_array($original) ? $original[0] : $original]['translation'] = $translation;
			}
		}
	}



	/**
	 * Metadata parser
	 *
	 * @param string $input
	 */
	private function parseMetadata($input)
	{
		$input = trim($input);

		$input = preg_split('/[\n,]+/', $input);
		foreach ($input as $metadata) {
			$pattern = ': ';
			$tmp = preg_split("($pattern)", $metadata);
			$this->metadata[trim($tmp[0])] = count($tmp) > 2 ? ltrim(strstr($metadata, $pattern), $pattern) : $tmp[1];
		}
	}



	/**
	 * Translates the given string.
	 *
	 * @param string $message
	 * @param int $form plural form (positive number)
	 * @return string
	 */
	public function translate($message, $form = 1)
	{
		$this->loadDictonary();

		$message = (string) $message;
		$message_plural = NULL;
		if (is_array($form) && $form !== NULL) {
			$message_plural = current($form);
			$form = end($form);
		}
		if (!is_int($form) || $form === NULL) {
			$form = 1;
		}

		if (!empty($message) && isset($this->dictionary[$message])) {
			$tmp = preg_replace('/([a-z]+)/', '$$1', "n=$form;".$this->metadata['Plural-Forms']);
			eval($tmp);


			$message = $this->dictionary[$message]['translation'];
			if (!empty($message))
				$message = (is_array($message) && $plural !== NULL && isset($message[$plural])) ? $message[$plural] : $message;
		} else {
			if (!Environment::getHttpResponse()->isSent() || Environment::getSession()->isStarted()) {
				$space = Environment::getSession(self::SESSION_NAMESPACE);
				if (!isset($space->newStrings))
					$space->newStrings = array();
				$space->newStrings[$message] = empty($message_plural) ? array($message) : array($message, $message_plural);
			}
			if ($form > 1 && !empty($message_plural))
				$message = $message_plural;
		}

		if (is_array($message))
			$message = current($message);

		$args = func_get_args();
		if (count($args) > 1) {
			array_shift($args);
			if (is_array(current($args)) || current($args) === NULL)
				array_shift($args);

			if (count($args) == 1 && is_array(current($args)))
				$args = current($args);

			$message = str_replace(array("%label", "%name", "%value"), array("#label", "#name", "#value"), $message);
			if (count($args) > 0 && $args != NULL);
				$message = vsprintf($message, $args);
			$message = str_replace(array("#label", "#name", "#value"), array("%label", "%name", "%value"), $message);
		}
		return $message;
	}



	/**
	 * Get count of plural forms
	 *
	 * @return int
	 */
	public function getVariantsCount()
	{
		$this->loadDictonary();

		if (isset($this->metadata['Plural-Forms'])) {
			return (int)substr($this->metadata['Plural-Forms'], 9, 1);
		}
		return 1;
	}



	/**
	 * Get translations strings
	 *
	 * @return array
	 */
	public function getStrings()
	{
		$this->loadDictonary();

		$result = array();

		$storage = Environment::getSession(self::SESSION_NAMESPACE);
		if (isset($storage->newStrings)) {
			foreach (array_keys($storage->newStrings) as $original) {
				if (trim($original) != "") {
					$result[$original] = FALSE;
				}
			}
		}

		foreach ($this->dictionary as $original => $data) {
			if (trim($original) != "") {
				$result[$original] = $data['translation'];
			}
		}

		return $result;
	}



	/**
	 * Set translation string(s)
	 *
	 * @param string|array $message original string(s)
	 * @param string|array $string translation string(s)
	 */
	public function setTranslation($message, $string)
	{
		$this->loadDictonary();

		$space = Environment::getSession(self::SESSION_NAMESPACE);
		if (isset($space->newStrings) && array_key_exists($message, $space->newStrings))
			$message = $space->newStrings[$message];

		$this->dictionary[is_array($message) ? $message[0] : $message]['original'] = (array) $message;
		$this->dictionary[is_array($message) ? $message[0] : $message]['translation'] = (array) $string;
	}



	/**
	 * Save dictionary
	 */
	public function save()
	{
		$this->loadDictonary();

		$this->buildMOFile($this->dirs[0]."/".$this->lang.".mo");
		$this->buildPOFile($this->dirs[0]."/".$this->lang.".po");

		$storage = Environment::getSession(self::SESSION_NAMESPACE);
		if (isset($storage->newStrings)) {
			unset($storage->newStrings);
		}
		if (self::$cache) {
			$cache = Environment::getCache(self::SESSION_NAMESPACE)
				->clean(array(\Nette\Caching\Cache::TAGS => 'dictionary-'.$this->lang));
		}
	}



	/**
	 * Generate gettext metadata array
	 *
	 * @return array
	 */
	private function generateMetadata()
	{
		$result = array();
		if (isset($this->metadata['Project-Id-Version']))
			$result[] = "Project-Id-Version: ".$this->metadata['Project-Id-Version'];
		else
			$result[] = "Project-Id-Version: ";
		if (isset($this->metadata['Report-Msgid-Bugs-To']))
			$result[] = "Report-Msgid-Bugs-To: ".$this->metadata['Report-Msgid-Bugs-To'];
		if (isset($this->metadata['POT-Creation-Date']))
			$result[] = "POT-Creation-Date: ".$this->metadata['POT-Creation-Date'];
		else
			$result[] = "POT-Creation-Date: ";
		$result[] = "PO-Revision-Date: ".date("Y-m-d H:iO");
		if (isset($this->metadata['Last-Translator']))
			$result[] = "Language-Team: ".$this->metadata['Language-Team'];
		else
			$result[] = "Language-Team: ";
		if (isset($this->metadata['MIME-Version']))
			$result[] = "MIME-Version: ".$this->metadata['MIME-Version'];
		else
			$result[] = "MIME-Version: 1.0";
		if (isset($this->metadata['Content-Type']))
			$result[] = "Content-Type: ".$this->metadata['Content-Type'];
		else
			$result[] = "Content-Type: text/plain; charset=UTF-8";
		if (isset($this->metadata['Content-Transfer-Encoding']))
			$result[] = "Content-Transfer-Encoding: ".$this->metadata['Content-Transfer-Encoding'];
		else
			$result[] = "Content-Transfer-Encoding: 8bit";
		if (isset($this->metadata['Plural-Forms']))
			$result[] = "Plural-Forms: ".$this->metadata['Plural-Forms'];
		else
			$result[] = "Plural-Forms: ";
		if (isset($this->metadata['X-Poedit-Language']))
			$result[] = "X-Poedit-Language: ".$this->metadata['X-Poedit-Language'];
		if (isset($this->metadata['X-Poedit-Country']))
			$result[] = "X-Poedit-Country: ".$this->metadata['X-Poedit-Country'];
		if (isset($this->metadata['X-Poedit-SourceCharset']))
			$result[] = "X-Poedit-SourceCharset: ".$this->metadata['X-Poedit-SourceCharset'];
		if (isset($this->metadata['X-Poedit-KeywordsList']))
			$result[] = "X-Poedit-KeywordsList: ".$this->metadata['X-Poedit-KeywordsList'];

		return $result;
	}



	/**
	 * Build gettext MO file
	 *
	 * @param string $file
	 */
	private function buildPOFile($file)
	{
		$po = "# Gettext keys exported by GettextTranslator and Translation Panel\n"
			."# Created: ".date('Y-m-d H:i:s')."\n".'msgid ""'."\n".'msgstr ""'."\n";
		$po .= '"'.implode('\n"'."\n".'"', $this->generateMetadata()).'\n"'."\n\n\n";
		foreach ($this->dictionary as $message => $data) {
			$po .= 'msgid "'.str_replace(array('"', "'"), array('\"', "\\'"), $message).'"'."\n";
			if (is_array($data['original']) && count($data['original']) > 1)
				$po .= 'msgid_plural "'.str_replace(array('"', "'"), array('\"', "\\'"), end($data['original'])).'"'."\n";
			if (!is_array($data['translation']))
				$po .= 'msgstr "'.str_replace(array('"', "'"), array('\"', "\\'"), $data['translation']).'"'."\n";
			elseif (count($data['translation']) < 2)
				$po .= 'msgstr "'.str_replace(array('"', "'"), array('\"', "\\'"), current($data['translation'])).'"'."\n";
			else {
				$i = 0;
				foreach ($data['translation'] as $string) {
					$po .= 'msgstr['.$i.'] "'.str_replace(array('"', "'"), array('\"', "\\'"), $string).'"'."\n";
					$i++;
				}
			}
			$po .= "\n";
		}

		$storage = Environment::getSession(self::SESSION_NAMESPACE);
		if (isset($storage->newStrings)) {
			foreach ($storage->newStrings as $original) {
				if (trim(current($original)) != "" && !\array_key_exists(current($original), $this->dictionary)) {
					$po .= 'msgid "'.str_replace(array('"', "'"), array('\"', "\\'"), current($original)).'"'."\n";
					if (count($original) > 1)
						$po .= 'msgid_plural "'.str_replace(array('"', "'"), array('\"', "\\'"), end($original)).'"'."\n";
					$po .= "\n";
				}
			}
		}

		file_put_contents($file, $po);
	}



	/**
	 * Build gettext MO file
	 *
	 * @param string $file
	 */
	private function buildMOFile($file)
	{
		ksort($this->dictionary);

		$metadata = implode("\n", $this->generateMetadata());
		$items = count($this->dictionary) + 1;
		$ids = String::chr(0x00);
		$strings = $metadata.String::chr(0x00);
		$idsOffsets = array(0, 28 + $items * 16);
		$stringsOffsets = array(array(0, strlen($metadata)));

		foreach ($this->dictionary as $key => $value) {
			$id = $key;
			if (is_array($value['original']) && count($value['original']) > 1)
				$id .= String::chr(0x00).end($value['original']);

			$string = implode(String::chr(0x00), $value['translation']);
			$idsOffsets[] = strlen($id);
			$idsOffsets[] = strlen($ids) + 28 + $items * 16;
			$stringsOffsets[] = array(strlen($strings), strlen($string));
			$ids .= $id.String::chr(0x00);
			$strings .= $string.String::chr(0x00);
		}

		$valuesOffsets = array();
		foreach ($stringsOffsets as $offset) {
			list ($all, $one) = $offset;
			$valuesOffsets[] = $one;
			$valuesOffsets[] = $all + strlen($ids) + 28 + $items * 16;
		}
		$offsets= array_merge($idsOffsets, $valuesOffsets);

		$mo = pack('Iiiiiii', 0x950412de, 0, $items, 28, 28 + $items * 8, 0, 28 + $items * 16);
		foreach ($offsets as $offset)
			$mo .= pack('i', $offset);

		file_put_contents($file, $mo.$ids.$strings);
	}



	/**
	 * Get translator
	 *
	 * @param string $translationsDir
	 * @param string $language
	 * @return NetteTranslator\Gettext
	 */
	public static function getTranslator($translationsDir, $language = NULL)
	{
		return new static($translationsDir, $language ?: 'en_US');
	}



	/**
	 * Returns current language
	 */
	public function getLang()
	{
		return $this->lang;
	}



	/**
	 * Sets a new language
	 */
	public function setLang($lang)
	{
		if($this->lang === $lang)
			return;

		$this->lang = $lang;
		$this->dictionary = array();
		$this->loaded = FALSE;

		$this->loadDictonary();
	}

}