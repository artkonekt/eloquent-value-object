<?php
/**
 * Contains the TestCase class.
 *
 * @copyright   Copyright (c) 2018 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2018-01-05
 *
 */


namespace Konekt\EloquentValueObject\Tests;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $capsule;

    protected function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        $this->capsule = new Capsule();
        $this->capsule->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);

        $this->capsule->setEventDispatcher(new Dispatcher());
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $this->capsule->schema()->dropIfExists('orders');
        $this->capsule->schema()->dropIfExists('clients');
        $this->capsule->schema()->dropIfExists('addresses');
    }
}
