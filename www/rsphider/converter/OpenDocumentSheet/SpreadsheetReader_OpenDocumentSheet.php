<?php
if (!class_exists('ODS_SpreadsheetReader'))
require_once dirname(__FILE__) . '/../ods_reader.php';

class SpreadsheetReader_OpenDocumentSheet extends ODS_SpreadsheetReader {
	protected $_odsXml;
	protected $_xsl;
	protected $_processor;
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->_odsXml = new DOMDocument;
		$this->_xsl = new DOMDocument;
		$this->_xsl->load(dirname(__FILE__) . '/extract_ods_tables.xslt');

		// Configure the transformer
		$this->_processor = new XSLTProcessor;
		$this->_processor->importStyleSheet($this->_xsl); // attach the xsl rules
	}

	/**
	 * $sheets = read('~/example.ods');
	 * $sheet = 0;
	 * $row = 0;
	 * $column = 0;
	 * echo $sheets[$sheet][$row][$column];
	 *
	 * @param $odsFilePath  File path of Open Document Sheet file.
	 * @param $returnType   Type of return value.
	 *                      'array':  Array. This is default.
	 *                      'string': XML string.
	 * @return FALSE or an array contains sheets.
	 */
	public function &read($odsFilePath, $returnType = 'array') {
		$ReturnFalse = FALSE;

		if ( !is_readable($odsFilePath) ) {
			return $ReturnFalse;
		}

		if (strncmp(PHP_VERSION, '4', 1)) :
		$zip = new ZipArchive; // PHP5 or later.
		if ($zip->open($odsFilePath) !== TRUE) {
			return $ReturnFalse;
		}
		$fp = $zip->getStream('content.xml');
		//fpassthru($fp);
		$xmlString = '';
		while($s = fgets($fp)) {
			$xmlString .= $s;
		}
		fclose($fp);
		$zip->close();
		else :
		$zip = zip_open($odsFilePath); // PHP4
		if (!is_resource($zip)) {
			return $ReturnFalse;
		}
		while($entry = zip_read($zip)) {
			if (zip_entry_name($entry) == 'content.xml')
			break;
		}
		$xmlString = '';
		while($s = zip_entry_read($entry)) {
			$xmlString .= $s;
		}
		zip_entry_close($entry);
		zip_close($zip);
		endif;

		$this->_odsXml->loadXML($xmlString);
		$xmlString = $this->_processor->transformToXML($this->_odsXml);

		if ($returnType == 'string') {
			return $xmlString;
		}

		return $this->_toArray($xmlString);
	}
}
?>
