<?php

namespace Otus\Diag;

use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;

class FileExceptionHandlerLogCustom extends FileExceptionHandlerLog
{
    protected $level;

	public function write($exception, $logType)
	{
		$text = ExceptionHandlerFormatter::format($exception, false, $this->level);

		$context = [
			'type' => static::logTypeToString($logType),
		];

		$logLevel = static::logTypeToLevel($logType);

		$message = "Otus - {date} - Host: {host} - {type} - {$text}\n";

		$this->logger->log($logLevel, $message, $context);
	}

	/**
	 * @deprecated
	 */
	protected function writeToLog($text)
	{
		$this->logger->debug($text);
	}
}