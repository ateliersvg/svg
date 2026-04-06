<?php

declare(strict_types=1);

namespace Atelier\Svg\Dumper;

use Atelier\Svg\Dumper\Formatter\XmlFormatter;
use Atelier\Svg\Dumper\Formatter\XmlFormatterInterface;

final class PrettyXmlDumper extends XmlDumper
{
    protected function createFormatter(): XmlFormatterInterface
    {
        return new XmlFormatter(
            pretty: true,
            preserveWhitespace: false
        );
    }
}
