<?php

namespace App\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsMiddleware;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;

#[AsMiddleware]
final class QueryCountingMiddleware implements Middleware
{
    public function __construct(private readonly QueryCounter $counter)
    {
    }

    public function wrap(Driver $driver): Driver
    {
        $counter = $this->counter;

        return new class($driver, $counter) extends AbstractDriverMiddleware {
            public function __construct(Driver $driver, private readonly QueryCounter $counter)
            {
                parent::__construct($driver);
            }

            public function connect(array $params): DriverConnection
            {
                $counter = $this->counter;

                return new class(parent::connect($params), $counter) extends AbstractConnectionMiddleware {
                    public function __construct(DriverConnection $connection, private readonly QueryCounter $counter)
                    {
                        parent::__construct($connection);
                    }

                    public function query(string $sql): Result
                    {
                        $this->counter->increment();

                        return parent::query($sql);
                    }

                    public function exec(string $sql): int|string
                    {
                        $this->counter->increment();

                        return parent::exec($sql);
                    }

                    public function prepare(string $sql): Statement
                    {
                        $counter = $this->counter;

                        return new class(parent::prepare($sql), $counter) extends AbstractStatementMiddleware {
                            public function __construct(Statement $statement, private readonly QueryCounter $counter)
                            {
                                parent::__construct($statement);
                            }

                            public function execute(): Result
                            {
                                $this->counter->increment();

                                return parent::execute();
                            }
                        };
                    }
                };
            }
        };
    }
}
