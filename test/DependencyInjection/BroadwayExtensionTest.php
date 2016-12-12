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

    private function assertDICAliasClass($aliasId, $class)
    {
        $definitionId = (string) $this->container->getAlias($aliasId);
        $this->assertDICDefinitionClass($this->container->getDefinition($definitionId), $class);
    }
}
