<?php

namespace Tests;

use Database\Seeders\FeatureTestSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Run a specific seeder before each test.
     *
     * @var string
     */
    protected $seeder = FeatureTestSeeder::class;
}
