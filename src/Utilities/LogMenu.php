<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Utilities;

use Arcanedev\LogViewer\Contracts\Utilities\LogMenu as LogMenuContract;
use Arcanedev\LogViewer\Contracts\Utilities\LogStyler as LogStylerContract;
use Arcanedev\LogViewer\Entities\Log;
use Illuminate\Contracts\Config\Repository as ConfigContract;

/**
 * Class     LogMenu
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogMenu implements LogMenuContract
{
    protected ConfigContract $config;

    protected LogStylerContract $styler;

    public function __construct(ConfigContract $config, LogStylerContract $styler)
    {
        $this->setConfig($config);
        $this->setLogStyler($styler);
    }

    public function setConfig(ConfigContract $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function setLogStyler(LogStylerContract $styler): static
    {
        $this->styler = $styler;

        return $this;
    }

    public function make(Log $log, bool $trans = true): array
    {
        $items = [];

        foreach ($log->tree($trans) as $level => $item) {
            $items[$level] = array_merge($item, [
                'url' => route('log-viewer::logs.show', [
                    'prefix' => $log->prefix,
                    'date' => $log->date,
                    'level' => $level === 'all' ? null : $level,
                ]),
                'icon' => $this->isIconsEnabled() ? $this->styler->icon($level)->toHtml() : '',
            ]);
        }

        return $items;
    }

    protected function isIconsEnabled(): bool
    {
        return (bool) $this->config('menu.icons-enabled', false);
    }

    protected function config(string $key, $default = null)
    {
        return $this->config->get("log-viewer.{$key}", $default);
    }
}
