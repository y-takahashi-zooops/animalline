<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Command;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Eccube\Service\EntityProxyService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateProxyCommand extends Command
{
    protected static $defaultName = 'eccube:generate:proxies';

    /**
     * @var EntityProxyService
     */
    private $entityProxyService;

    public function __construct(EntityProxyService $entityProxyService, string $projectDir,  array $enabledPlugins)
    {
        parent::__construct();
        $this->entityProxyService = $entityProxyService;
        $this->projectDir = $projectDir;
        $this->enabledPlugins = $enabledPlugins;
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate entity proxies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // アノテーションを読み込めるように設定.
        AnnotationRegistry::registerAutoloadNamespace('Eccube\Annotation', __DIR__.'/../../../src');

        $includeDirs = [$this->projectDir.'/app/Customize/Entity'];

        foreach ($this->enabledPlugins as $code) {
            if (file_exists($this->projectDir.'/app/Plugin/'.$code.'/Entity')) {
                $includeDirs[] = $this->projectDir.'/app/Plugin/'.$code.'/Entity';
            }
        }

        $this->entityProxyService->generate(
            $includeDirs,
            [],
            $this->projectDir.'/app/proxy/entity',
            $output
        );
    }
}
