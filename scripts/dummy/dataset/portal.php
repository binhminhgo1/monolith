<?php

echo \go1\util_dataset\staff\es_dumper\ElasticSearchPortalDumper::dump($app['go1.client.es'], $portalId);
