<?php

declare(strict_types=1);
/**
 * Created by solutionDrive GmbH
 *
 * @author    Matthias Alt <alt@solutiondrive.de>
 * @date      12.11.17
 * @time:     11:44
 *
 * @copyright 2017 solutionDrive GmbH
 */

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

interface ResourceProcessorInterface
{
    public function process(array $data);
}
