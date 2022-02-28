<?php

namespace Stellif\Stellif\Templating;

class BlocksParser
{
    public function __construct(
        private readonly string $content,
    ) {
    }

    private function paragraph(string $value, array $meta = null): string
    {
        return "<p>${value}</p>";
    }

    public function parse(): string
    {
        $contentItems = json_decode($this->content, true);
        $result = '';

        foreach ($contentItems as $contentItem) {
            if (method_exists($this, $contentItem['block'])) {
                $result .= $this->{$contentItem['block']}($contentItem['value'], $contentItem['meta'] ?? null);
            }
        }

        return $result;
    }
}
