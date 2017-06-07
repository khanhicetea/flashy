<?php
namespace Flashy;

use DI\ContainerBuilder;

interface ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []) : void;
}
