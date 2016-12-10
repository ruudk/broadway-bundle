<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Bundle\BroadwayBundle\DependencyInjection;

use IC\Bundle\Base\TestBundle\Test\DependencyInjection\ExtensionTestCase;

class BroadwayExtensionTest extends ExtensionTestCase
{
    private $extension;

    public function setUp()
    {
        parent::setUp();
        $this->extension = new BroadwayExtension();
    }

    /**
     * @test
     * @dataProvider readModelConfigurationToRepositoryMapping
     */
    public function read_model_repository_factory_set_to_configured_repository_factory($repoFactory, $class)
    {
        $configuration = ['read_model' => ['repository' => $repoFactory]];

        $this->load($this->extension, $configuration);

        $this->assertDICAliasClass('broadway.read_model.repository_factory', $class);
    }

    public function readModelConfigurationToRepositoryMapping()
    {
        return [
            ['in_memory',     'Broadway\ReadModel\InMemory\InMemoryRepositoryFactory'],
            ['elasticsearch', 'Broadway\ReadModel\ElasticSearch\ElasticSearchRepositoryFactory'],
        ];
    }

    /**
     * @test
     */
    public function default_read_model_repository_factory_is_elasticsearch()
    {
        $this->load($this->extension, []);

        $this->assertDICAliasClass('broadway.read_model.repository_factory', 'Broadway\ReadModel\ElasticSearch\ElasticSearchRepositoryFactory');
    }

    /**
     * @test
     */
    public function it_enables_the_simple_command_bus()
    {
        $configuration = ['command_handling' => ['logger' => false]];

        $this->load($this->extension, $configuration);
        $this->assertDICAliasClass('broadway.command_handling.command_bus', 'Broadway\CommandHandling\SimpleCommandBus');
    }

    /**
     * @test
     */
    public function it_enables_the_logging_command_bus()
    {
        $configuration = ['command_handling' => ['logger' => 'service']];

        $this->load($this->extension, $configuration);
        $this->assertDICAliasClass('broadway.command_handling.command_bus', 'Broadway\CommandHandling\EventDispatchingCommandBus');
    }

    /**
     * @test
     */
    public function it_creates_an_auditing_logger_alias()
    {
        $configuration = ['command_handling' => ['logger' => 'service']];

        $this->load($this->extension, $configuration);

        $auditingLoggerAlias = $this->container->getAlias('broadway.auditing.logger');
        $this->assertEquals('service', (string) $auditingLoggerAlias);
    }

    /**
     * @test
     */
    public function it_can_enable_the_event_dispatching_command_bus_but_not_the_logger()
    {
        $configuration = ['command_handling' => ['dispatch_events' => true, 'logger' => false]];

        $this->load($this->extension, $configuration);

        $this->assertSame(
            'broadway.command_handling.event_dispatching_command_bus',
            (string) $this->container->getAlias('broadway.command_handling.command_bus')
        );
        $this->assertFalse($this->container->hasDefinition('broadway.auditing.command_logger'));
    }

    /**
     * @test
     */
    public function it_has_dbal_as_default_event_store()
    {
        $this->load($this->extension, array());

        $this->assertTrue(
            $this->container->hasDefinition('broadway.event_store.dbal')
        );
        $this->assertTrue($this->container->hasAlias('broadway.event_store'));
        $this->assertEquals(
            'broadway.event_store.dbal',
            $this->container->getAlias('broadway.event_store')
        );
    }

    /**
     * @test
     */
    public function disabling_dbal_event_store_does_not_load_its_definitions()
    {
        $this->load(
            $this->extension,
            array('event_store' => array('dbal' => array('enabled' => false)))
        );

        $this->assertFalse(
            $this->container->hasDefinition('broadway.event_store.dbal')
        );
        $this->assertFalse($this->container->hasAlias('broadway.event_store'));
    }

    private function assertDICAliasClass($aliasId, $class)
    {
        $definitionId = (string) $this->container->getAlias($aliasId);
        $this->assertDICDefinitionClass($this->container->getDefinition($definitionId), $class);
    }
}
