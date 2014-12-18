<?php
/*
 Copyright (C) 2006 Edward Finkler

 Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer
 in the documentation  and/or other materials provided with the distribution.
 3. The name of the author may not be used to endorse or promote products derived from this software without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
 BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 * Main class file
 *
 * @package PhpSecInfo
 * @author Ed Finkler <coj@funkatron.com>
 */


/**
 * The default language setting if none is set/retrievable
 *
 */
define ('PHPSECINFO_LANG_DEFAULT', 'en');

/**
 * a general version string to differentiate releases
 *
 */
define ('PHPSECINFO_VERSION', '0.2.1');

/**
 * a YYYYMMDD date string to indicate "build" date
 *
 */
define ('PHPSECINFO_BUILD', '20070406');

/**
 * Homepage for phpsecinfo project
 *
 */
define ('PHPSECINFO_URL', 'http://phpsecinfo.com');

/**
 * This is the main class for the phpsecinfo system.  It's responsible for
 * dynamically loading tests, running those tests, and generating the results
 * output
 *
 * Example:
 * <code>
 * <?php require_once('PhpSecInfo/PhpSecInfo.php'); ?>
 * <?php phpsecinfo(); ?>
 * </code>
 *
 * If you want to capture the output, or just grab the test results and display them
 * in your own way, you'll need to do slightly more work.
 *
 * Example:
 * <code>
 * require_once('PhpSecInfo/PhpSecInfo.php');
 * // instantiate the class
 * $psi = new PhpSecInfo();
 *
 * // load and run all tests
 * $psi->loadAndRun();
 *
 * // grab the results as a multidimensional array
 * $results = $psi->getResultsAsArray();
 * echo "<pre>"; echo print_r($results, true); echo "</pre>";
 *
 * // grab the standard results output as a string
 * $html = $psi->getOutput();
 *
 * // send it to the browser
 * echo $html;
 * </code>
 *
 *
 * The procedural function "phpsecinfo" is defined below this class.
 * @see phpsecinfo()
 *
 * @author Ed Finkler <coj@funkatron.com>
 *
 * see CHANGELOG for changes
 *
 */
class PhpSecInfo
{

	/**
	 * An array of tests to run
	 *
	 * @var array PhpSecInfo_Test
	 */
	var $tests_to_run = array();

	/**
	 * An array of results.  Each result is an associative array:
	 * <code>
	 * $result['result'] = PHPSECINFO_TEST_RESULT_NOTICE;
	 * $result['message'] = "a string describing the test results and what they mean";
	 * </code>
	 *
	 * @var array
	 */
	var $test_results = array();


	/**
	 * An array of tests that were not run
	 *
	 * <code>
	 * $result['result'] = PHPSECINFO_TEST_RESULT_NOTRUN;
	 * $result['message'] = "a string explaining why the test was not run";
	 * </code>
	 *
	 * @var array
	 */
	var $tests_not_run = array();


	/**
	 * The language code used.  Defaults to PHPSECINFO_LANG_DEFAULT, which
	 * is 'en'
	 *
	 * @var string
	 * @see PHPSECINFO_LANG_DEFAULT
	 */
	var $language = PHPSECINFO_LANG_DEFAULT;


	/**
	 * An array of integers recording the number of test results in each category.  Categories can include
	 * some or all of the PHPSECINFO_TEST_* constants.  Constants are the keys, # of results are the values.
	 *
	 * @var array
	 */
	var $result_counts = array();


	/**
	 * The number of tests that have been run
	 *
	 * @var integer
	 */
	var $num_tests_run = 0;


	/**
	 * Constructor
	 *
	 * @return PhpSecInfo
	 */
	function PhpSecInfo() {

	}


	/**
	 * recurses through the Test subdir and includes classes in each test group subdir,
	 * then builds an array of classnames for the tests that will be run
	 *
	 */
	function loadTests() {

		$test_root = dir(dirname(__FILE__).DIRECTORY_SEPARATOR.'Test');

		//echo "<pre>"; echo print_r($test_root, true); echo "</pre>";

		while (false !== ($entry = $test_root->read())) {
			if ( is_dir($test_root->path.DIRECTORY_SEPARATOR.$entry) && !preg_match('|^\.(.*)$|', $entry) ) {
				$test_dirs[] = $entry;
			}
		}
		//echo "<pre>"; echo print_r($test_dirs, true); echo "</pre>";

		// include_once all files in each test dir
		foreach ($test_dirs as $test_dir) {
			$this_dir = dir($test_root->path.DIRECTORY_SEPARATOR.$test_dir);

			while (false !== ($entry = $this_dir->read())) {
				if (!is_dir($this_dir->path.DIRECTORY_SEPARATOR.$entry)) {
					include_once $this_dir->path.DIRECTORY_SEPARATOR.$entry;
					$classNames[] = "PhpSecInfo_Test_".$test_dir."_".basename($entry, '.php');
				}
			}

		}

		// modded this to not throw a PHP5 STRICT notice, although I don't like passing by value here
		$this->tests_to_run = $classNames;
	}


	/**
	 * This runs the tests in the tests_to_run array and
	 * places returned data in the following arrays/scalars:
	 * - $this->test_results
	 * - $this->result_counts
	 * - $this->num_tests_run
	 * - $this->tests_not_run;
	 *
	 */
	function runTests() {
		// initialize a bunch of arrays
		$this->test_results  = array();
		$this->result_counts = array();
		$this->result_counts[PHPSECINFO_TEST_RESULT_NOTRUN] = 0;
		$this->num_tests_run = 0;

		foreach ($this->tests_to_run as $testClass) {

			/**
			 * @var $test PhpSecInfo_Test
			 */
			$test = new $testClass();

			if ($test->isTestable()) {
				$test->test();
				$rs = array(	'result' => $test->getResult(),
							'message' => $test->getMessage(),
							'value_current' => $test->getCurrentTestValue(),
							'value_recommended' => $test->getRecommendedTestValue(),
							'moreinfo_url' => $test->getMoreInfoURL(),
				);
				$this->test_results[$test->getTestGroup()][$test->getTestName()] = $rs;

				// initialize if not yet set
				if (!isset ($this->result_counts[$rs['result']]) ) {
					$this->result_counts[$rs['result']] = 0;
				}

				$this->result_counts[$rs['result']]++;
				$this->num_tests_run++;
			} else {
				$rs = array(	'result' => $test->getResult(),
							'message' => $test->getMessage(),
							'value_current' => NULL,
							'value_recommended' => NULL,
							'moreinfo_url' => $test->getMoreInfoURL(),
				);
				$this->result_counts[PHPSECINFO_TEST_RESULT_NOTRUN]++;
				$this->tests_not_run[$test->getTestGroup()."::".$test->getTestName()] = $rs;
			}
		}
	}


	/**
	 * This is the main output method.  The look and feel mimics phpinfo()
	 *
	 */
	function renderOutput($page_title="Security Information About PHP") {

		/**
		 * We need to use PhpSecInfo_Test::getBooleanIniValue() below
		 * @see PhpSecInfo_Test::getBooleanIniValue()
		 */
		if (!class_exists('PhpSecInfo_Test')) {
			include( dirname(__FILE__).DIRECTORY_SEPARATOR.'Test'.DIRECTORY_SEPARATOR.'Test.php');
		}

		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $page_title ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<style type="text/css">
.phpblue { #777BB4
	
}

/*
	#706464
	#C7C6B3
	#7B8489
	#646B70
	*/
BODY {
	background-color: #C7C6B3;
	color: #333333;
	margin: 0;
	padding: 0;
	text-align: center;
}

BODY,TD,TH,H1,H2 {
	font-family: Helvetica, Arial, Sans-serif;
}

DIV.logo {
	float: right;
}

A:link,A:hover,A:visited {
	color: #000099;
	text-decoration: none;
}

A:hover {
	text-decoration: underline !important;
}

DIV.container {
	text-align: center;
	width: 650px;
	margin-left: auto;
	margin-right: auto;
}

DIV.header {
	width: 100%;
	text-align: left;
	border-collapse: collapse;
}

DIV.header {
	background-color: #4C5B74;
	color: white;
	border-bottom: 3px solid #333333;
	padding: .5em;
}

DIV.header H1,DIV.header H2 {
	font-size: 0.8em;
	padding: 0;
	margin: 0;
}

DIV.header H2 {
	font-size: 0.7em;
}

DIV.header a:link,DIV.header a:visited,DIV.header a:hover {
	color: #ffff99;
}

H2.result-header {
	margin: 1em 0 .5em 0;
}

TABLE.results {
	border-collapse: collapse;
	width: 100%;
	text-align: left;
}

TD,TH {
	padding: 0.5em;
	border: 2px solid #333333;
}

TR.header {
	background-color: #706464;
	color: white;
}

TD.label {
	text-align: top;
	font-weight: bold;
	background-color: #7B8489;
	border: 2px solid #333333;
}

TD.value {
	border: 2px solid #333333
}

.centered {
	text-align: center;
}

.centered TABLE {
	text-align: left;
}

.centered TH {
	text-align: center;
}

.result {
	font-size: 1.0em;
	font-weight: bold;
	margin-bottom: .5em;
}

.message {
	line-height: 1.4em;
}

TABLE.values {
	padding: .5em;
	margin: .5em;
	text-align: left;
	margin: none;
	width: 90%;
}

TABLE.values TD {
	font-size: .8em;
	border: none;
	padding: .4em;
}

TABLE.values TD.label {
	font-weight: bold;
	text-align: right;
	width: 40%;
}

DIV.moreinfo {
	text-align: right;
}

.value-ok {
	background-color: #009900;
	color: #ffffff;
}

.value-ok a:link,.value-ok a:hover,.value-ok a:visited {
	color: #FFFF99;
	font-weight: bold;
	background-color: transparent;
	text-decoration: none;
}

.value-ok table td {
	background-color: #33AA33;
	color: #ffffff;
}

.value-notice {
	background-color: #FFA500;
	color: #000000;
}

.value-notice a:link,.value-notice a:hover,.value-notice a:visited {
	color: #000099;
	font-weight: bold;
	background-color: transparent;
	text-decoration: none;
}

.value-notice td {
	background-color: #FFC933;
	color: #000000;
}

.value-warn {
	background-color: #990000;
	color: #ffffff;
}

.value-warn a:link,.value-warn a:hover,.value-warn a:visited {
	color: #FFFF99;
	font-weight: bold;
	background-color: transparent;
	text-decoration: none;
}

.value-warn td {
	background-color: #AA3333;
	color: #ffffff;
}

.value-notrun {
	background-color: #cccccc;
	color: #000000;
}

.value-notrun a:link,.value-notrun a:hover,.value-notrun a:visited {
	color: #000099;
	font-weight: bold;
	background-color: transparent;
	text-decoration: none;
}

.value-notrun td {
	background-color: #dddddd;
	color: #000000;
}

.value-error {
	background-color: #F6AE15;
	color: #000000;
	font-weight: bold;
}

.value-error td {
	background-color: #F6AE15;
	color: #000000;
}
</style>

</head>
<body>

<div class="container"><?php
foreach ($this->test_results as $group_name=>$group_results) {
	$this->_outputRenderTable($group_name, $group_results);
}

$this->_outputRenderNotRunTable();

$this->_outputRenderStatsTable();

?></div>
</body>
</html>
<?php
	}


	/**
	 * This is a helper method that makes it easy to output tables of test results
	 * for a given test group
	 *
	 * @param string $group_name
	 * @param array $group_results
	 */
	function _outputRenderTable($group_name, $group_results) {

		// exit out if $group_results was empty or not an array.  This sorta seems a little hacky...
		if (!is_array($group_results) || sizeof($group_results) < 1) {
			return false;
		}

		ksort($group_results);

		?>
<h2 class="result-header"><?php echo htmlspecialchars($group_name, ENT_QUOTES) ?></h2>

<table class="results">
	<tr class="header">
		<th>Test</th>
		<th>Result</th>
	</tr>
	<?php foreach ($group_results as $test_name=>$test_results): ?>

	<tr>
		<td class="label"><?php echo htmlspecialchars($test_name, ENT_QUOTES) ?></td>
		<td
			class="value <?php echo $this->_outputGetCssClassFromResult($test_results['result']) ?>">
			<?php if ($group_name != 'Test Results Summary'): ?>
		<div class="result"><?php echo $this->_outputGetResultTypeFromCode($test_results['result']) ?></div>
		<?php endif; ?>
		<div class="message"><?php echo $test_results['message'] ?></div>

		<?php if ( isset($test_results['value_current'] ) || isset($test_results['value_recommended']) ): ?>
		<table class="values">
		<?php if (isset($test_results['value_current'])): ?>
			<tr>
				<td class="label">Current Value:</td>
				<td><?php echo $test_results['value_current'] ?></td>
			</tr>
			<?php endif;?>
			<?php if (isset($test_results['value_recommended'])): ?>
			<tr>
				<td class="label">Recommended Value:</td>
				<td><?php echo $test_results['value_recommended'] ?></td>
			</tr>
			<?php endif; ?>
		</table>
		<?php endif; ?> <?php if (isset($test_results['moreinfo_url']) && $test_results['moreinfo_url']): ?>
		<div class="moreinfo"><a
			href="<?php echo $test_results['moreinfo_url']; ?>">More information
		&raquo;</a></div>
		<?php endif; ?></td>
	</tr>

	<?php endforeach; ?>
</table>
<br />

	<?php
	return true;
	}



	/**
	 * This outputs a table containing a summary of the test results (counts and % in each result type)
	 *
	 * @see PHPSecInfo::_outputRenderTable()
	 * @see PHPSecInfo::_outputGetResultTypeFromCode()
	 */
	function _outputRenderStatsTable() {

		foreach($this->result_counts as $code=>$val) {
			if ($code != PHPSECINFO_TEST_RESULT_NOTRUN) {
				$percentage = round($val/$this->num_tests_run * 100,2);

				$stats[$this->_outputGetResultTypeFromCode($code)] = array( 'count' => $val,
																'result' => $code,
																'message' => "$val out of {$this->num_tests_run} ($percentage%)");
			}
		}

		$this->_outputRenderTable('Test Results Summary', $stats);

	}



	/**
	 * This outputs a table containing a summary or test that were not executed, and the reasons why they were skipped
	 *
	 * @see PHPSecInfo::_outputRenderTable()
	 */
	function _outputRenderNotRunTable() {

		$this->_outputRenderTable('Tests Not Run', $this->tests_not_run);

	}




	/**
	 * This is a helper function that returns a CSS class corresponding to
	 * the result code the test returned.  This allows us to color-code
	 * results
	 *
	 * @param integer $code
	 * @return string
	 */
	function _outputGetCssClassFromResult($code) {

		switch ($code) {
			case PHPSECINFO_TEST_RESULT_OK:
				return 'value-ok';
				break;

			case PHPSECINFO_TEST_RESULT_NOTICE:
				return 'value-notice';
				break;

			case PHPSECINFO_TEST_RESULT_WARN:
				return 'value-warn';
				break;

			case PHPSECINFO_TEST_RESULT_NOTRUN:
				return 'value-notrun';
				break;

			case PHPSECINFO_TEST_RESULT_ERROR:
				return 'value-error';
				break;

			default:
				return 'value-notrun';
				break;
		}

	}



	/**
	 * This is a helper function that returns a label string corresponding to
	 * the result code the test returned.  This is mainly used for the Test
	 * Results Summary table.
	 *
	 * @see PHPSecInfo::_outputRenderStatsTable()
	 * @param integer $code
	 * @return string
	 */
	function _outputGetResultTypeFromCode($code) {

		switch ($code) {
			case PHPSECINFO_TEST_RESULT_OK:
				return 'Pass';
				break;

			case PHPSECINFO_TEST_RESULT_NOTICE:
				return 'Notice';
				break;

			case PHPSECINFO_TEST_RESULT_WARN:
				return 'Warning';
				break;

			case PHPSECINFO_TEST_RESULT_NOTRUN:
				return 'Not Run';
				break;

			case PHPSECINFO_TEST_RESULT_ERROR:
				return 'Error';
				break;

			default:
				return 'Invalid Result Code';
				break;
		}

	}


	/**
	 * Loads and runs all the tests
	 *
	 * As loading, then running, is a pretty common process, this saves a extra method call
	 *
	 * @since 0.1.1
	 *
	 */
	function loadAndRun() {
		$this->loadTests();
		$this->runTests();
	}


	/**
	 * returns an associative array of test data.  Four keys are set:
	 * - test_results  (array)
	 * - tests_not_run (array)
	 * - result_counts (array)
	 * - num_tests_run (integer)
	 *
	 * note that this must be called after tests are loaded and run
	 *
	 * @since 0.1.1
	 * @return array
	 */
	function getResultsAsArray() {
		$results = array();

		$results['test_results'] = $this->test_results;
		$results['tests_not_run'] = $this->tests_not_run;
		$results['result_counts'] = $this->result_counts;
		$results['num_tests_run'] = $this->num_tests_run;

		return $results;
	}



	/**
	 * returns the standard output as a string instead of echoing it to the browser
	 *
	 * note that this must be called after tests are loaded and run
	 *
	 * @since 0.1.1
	 *
	 * @return string
	 */
	function getOutput() {
		ob_start();
		$this->renderOutput();
		$output = ob_get_clean();
		return $output;
	}



}




/**
 * A globally-available function that runs the tests and creates the result page
 *
 */
function phpsecinfo() {
	// modded this to not throw a PHP5 STRICT notice, although I don't like passing by value here
	$psi = new PhpSecInfo();
	$psi->loadAndRun();
	$psi->renderOutput();
}

