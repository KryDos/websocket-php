<?php

/**
 * This file is used for the tests, but can also serve as an example of a WebSocket\Server.
 */

$GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'] = dirname(dirname(__FILE__)) . '/build/tmp';

require(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

use WebSocket\Server;

$server = new Server(array('timeout' => 2));

echo $server->getPort(), "\n";

while ($connection = $server->accept()) {
  $test_id = $server->getPath();
  $test_id = substr($test_id, 1);

  xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
  PHPUnit_Extensions_SeleniumCommon_ExitHandler::init();

  try {
    while(1) {
      $message = $server->receive();
      echo "Received $message\n\n";

      if ($message === 'exit') {
        echo microtime(true), " Client told me to quit.  Bye bye.\n";
        echo microtime(true), " Close response: ", $server->close(), "\n";
        echo microtime(true), " Close status: ", $server->getCloseStatus(), "\n";
        save_coverage_data($test_id);
        exit;
      }

      $server->send($message);
    }
  }
  catch (WebSocket\ConnectionException $e) {
    echo "\n", microtime(true), " Client died: $e\n";
    save_coverage_data($test_id);
  }
}

exit;


function save_coverage_data($test_id) {
  $data = xdebug_get_code_coverage();
  xdebug_stop_code_coverage();

  if (!is_dir($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'])) {
    mkdir($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'], 0777, true);
  }
  $file = $GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'] . '/' . $test_id
    . '.' . md5(uniqid(rand(), true));

  echo "Saving coverage data to $file...\n";
  file_put_contents($file, serialize($data));
}
