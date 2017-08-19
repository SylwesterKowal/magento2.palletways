<?php

namespace Wm21w\Palletways\Cron;
 
class Palletways
{
	protected $logger;
 
	public function __construct(
		\Psr\Log\LoggerInterface $loggerInterface
	) {
		$this->logger = $loggerInterface;
	}
 
	public function execute() {

		//test command line
        //php bin/magento cron:run --group="wm21w_palletways_cron_group"
		//$this->logger->debug('Wm21w\Palletways\Cron\Palletways');

	}
}