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

namespace Eccube\Doctrine\Common\CsvDataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use SplFileObject;

/**
 * CSVファイルを扱うためのフィクスチャ.
 *
 * @see https://github.com/doctrine/data-fixtures/blob/master/lib/Doctrine/Common/DataFixtures/FixtureInterface.php
 */
class CsvFixture implements FixtureInterface
{
    /**
     * @var \SplFileObject
     */
    // protected $file;
    private ?SplFileObject $file = null;
    private Connection $connection;

    /**
     * CsvFixture constructor.
     *
     * @param \SplFileObject|null $file
     */
    public function __construct(Connection $connection, ?\SplFileObject $file = null)
    {
        $this->connection = $connection;
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        if ($this->file === null) {
            throw new \LogicException('CSVファイルかDIコンテナが設定されていません。');
        }

        if ('\\' === DIRECTORY_SEPARATOR) {
            setlocale(LC_ALL, 'English_United States.1252');
        }

        $this->file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::DROP_NEW_LINE
        );

        $headers = $this->file->current();
        $this->file->next();

        $table_name = str_replace('.'.$this->file->getExtension(), '', $this->file->getFilename());
        $sql = $this->getSql($table_name, $headers);

        /** @var Connection $connection */
        $connection = $this->connection;
        $connection->beginTransaction();

        if ('mysql' === $connection->getDatabasePlatform()->getName()) {
            $connection->exec("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';");
        }

        $prepare = $connection->prepare($sql);

        while ($rows = $this->file->current()) {
            $index = 1;
            foreach ($rows as $col) {
                $prepare->bindValue($index, $col === '' ? null : $col);
                $index++;
            }
            $prepare->executeStatement();
            $this->file->next();

            $seconds = is_numeric(ini_get('max_execution_time'))
                ? intval(ini_get('max_execution_time'))
                : intval(get_cfg_var('max_execution_time'));
            set_time_limit($seconds);
        }

        $connection->commit();

        if ('postgresql' === $connection->getDatabasePlatform()->getName()) {
            $schemaManager = method_exists($connection, 'createSchemaManager')
                ? $connection->createSchemaManager()
                : $connection->getSchemaManager(); // 互換性のため

            $table = $schemaManager->introspectTable($table_name);

            if (!$table->hasPrimaryKey()) {
                return;
            }

            $pkColumns = $table->getPrimaryKey()->getColumns();
            if (count($pkColumns) !== 1) {
                return;
            }

            $pk_name = $pkColumns[0];
            $sequence_name = sprintf('%s_%s_seq', $table_name, $pk_name);

            $sql = 'SELECT COUNT(*) FROM information_schema.sequences WHERE sequence_name = ?';
            $count = $connection->fetchOne($sql, [$sequence_name]);

            if ($count < 1) {
                return;
            }

            $max = $connection->fetchOne(sprintf('SELECT MAX(%s) FROM %s', $pk_name, $table_name));

            $sql = is_null($max)
                ? sprintf("SELECT SETVAL('%s', 1, false)", $sequence_name)
                : sprintf("SELECT SETVAL('%s', %s)", $sequence_name, $max);

            $connection->executeQuery($sql);
        }
    }

    public function getSql(string $table_name, array $headers): string
    {
        return 'INSERT INTO '.$table_name.' ('.implode(', ', $headers).') VALUES ('.implode(', ', array_fill(0, count($headers), '?')).')';
    }

    public function getFile(): ?SplFileObject
    {
        return $this->file;
    }
}