<?php

namespace Arcanedev\LogViewer\Entities;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\HtmlString;
use JsonSerializable;

/**
 * Class     LogEntry
 *
 * @package  Arcanedev\LogViewer\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntry implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * Flag to encode context
     */
    public const JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    /**
     * Regex to search groups after date
     *
     * @var string
     */
    protected static $regex = '/(?:\[([^\[\]]*?)\])?(?:\[([^\[\]]*?)\])? ([a-z]+)\.([A-Z]+): (.*)/';

    /**
     * Group numbers matched to its setter methods
     *
     * @var array
     */
    protected static $propertyGroups = [
        3 => 'setEnv',
        4 => 'setLevel',
    ];

    /**
     * Group numbers matched to keys on array of extra properties
     *
     * @var array
     */
    protected static $extraGroups = [
        1 => 'uuid',
        2 => 'parentUuid',
    ];

    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var string */
    public $env;

    /** @var string */
    public $level;

    /** @var \Carbon\Carbon|callable */
    protected $datetime;

    /** @var string */
    public $header;

    /** @var string */
    public $stack;

    /** @var array */
    public $context = [];

    /** @var array */
    public $extra = [];

    /**
     * Configures cached config from `log-viewer` config
     *
     * @return void
     */
    public static function configure(): void
    {
        static::$regex = '/' . config('log-viewer.parser.regex') . '/';
        foreach (config('log-viewer.parser.property_groups') as $group => $property) {
            static::$propertyGroups[$group] = 'set' . ucfirst($property);
        }
        static::$extraGroups = config('log-viewer.parser.extra_groups');
    }

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Construct the log entry instance.
     *
     * @param  array       $header
     * @param  string|null  $stack
     */
    public function __construct(array $header, $stack = null)
    {
        $this->setStack($stack);
        $this->setHeader($header);
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Magic isset for extra parameters, configured via `parser.extra_groups`
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name): bool
    {
        return isset($this->extra[$name]);
    }

    /**
     * Magic getter for extra parameters, configured via `parser.extra_groups`
     *
     * @param string $name
     * @return string|null
     */
    public function __get(string $name): ?string
    {
        return $this->extra[$name] ?? null;
    }

    /**
     * Set entry environment.
     *
     * @param  string  $env
     *
     * @return self
     */
    protected function setEnv($env)
    {
        $this->env = $env;

        return $this;
    }

    /**
     * Set the entry level.
     *
     * @param  string  $level
     *
     * @return self
     */
    protected function setLevel($level)
    {
        $this->level = strtolower($level);

        return $this;
    }

    /**
     * Get the entry date time.
     *
     * @param  string  $format
     * @param  string  $datetime
     *
     * @return \Carbon\Carbon
     */
    public function getDatetime(): Carbon
    {
        if (is_callable($this->datetime)) {
            $this->datetime = ($this->datetime)();
        }

        return $this->datetime;
    }

    /**
     * Set the entry date time.
     *
     * @param  string  $format
     * @param  string  $datetime
     *
     * @return \Arcanedev\LogViewer\Entities\LogEntry
     */
    protected function setDatetime($format, $datetime)
    {
        // defer Carbon instances as they are too heavy
        $this->datetime = function () use ($format, $datetime) {
            return Carbon::createFromFormat($format, $datetime);
        };

        return $this;
    }

    /**
     * Set the entry header.
     *
     * @param  array  $header
     *
     * @return self
     */
    protected function setHeader(array $header)
    {
        $this->setDatetime(...$this->extractDatetime($header));

        $reminder = array_pop($header);
        preg_match_all(static::$regex, $reminder, $matches);
        foreach ($matches as $index => $value) {
            if (!empty($value)) {
                if (isset(static::$propertyGroups[$index])) {
                    call_user_func([$this, static::$propertyGroups[$index]], $value[0]);
                }
                if (isset(static::$extraGroups[$index]) && $value[0]) {
                    $this->extra[static::$extraGroups[$index]] = $value[0];
                }
            }
        }

        $reminder = array_pop($matches)[0];

        $regex = '/{(?:[^{}]|(?R))*}/xm';
        $reminder = preg_replace_callback($regex, function ($replacable) {
            if (!is_null($context = json_decode($replacable[0], true))) {
                $this->setContext($context);
                return '';
            }
            return $replacable[0];
        }, $reminder);
        if (!$this->context && !strpos($this->stack, '>>>>>>>>')) {
            $this->stack = preg_replace_callback($regex, function ($replacable) {
                if (!is_null($context = json_decode($replacable[0], true))) {
                    $this->setContext($context);
                    return '';
                }
                return $replacable[0];
            }, $this->stack);
        }

        $this->header = trim($reminder);

        return $this;
    }

    /**
     * Set the entry stack.
     *
     * @param  string  $stack
     *
     * @return self
     */
    protected function setStack($stack)
    {
        $this->stack = $stack;

        return $this;
    }

    /**
     * Set the context.
     *
     * @param  array  $context
     *
     * @return $this
     */
    protected function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get translated level name with icon.
     *
     * @return string
     */
    public function level(): string
    {
        return $this->icon()->toHtml() . ' ' . $this->name();
    }

    /**
     * Get translated level name.
     *
     * @return string
     */
    public function name(): string
    {
        return log_levels()->get($this->level);
    }

    /**
     * Get level icon.
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function icon(): HtmlString
    {
        return log_styler()->icon($this->level);
    }

    /**
     * Get the entry stack.
     *
     * @return string
     */
    public function stack(): string
    {
        return trim(htmlentities($this->stack));
    }

    /**
     * Get the entry context as json pretty print.
     *
     * @return string
     */
    public function context(): string
    {
        return json_encode($this->context, static::JSON_FLAGS);
    }

    /* -----------------------------------------------------------------
     |  Convert Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the log entry as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'uuid'    => $this->uuid,
            'level'    => $this->level,
            'datetime' => $this->getDatetime()->format('Y-m-d H:i:s'),
            'header'   => $this->header,
            'stack'    => $this->stack,
        ];
    }

    /**
     * Convert the log entry to its JSON representation.
     *
     * @param  int  $options
     *
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Serialize the log entry object to json data.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if the entry has a stack.
     *
     * @return bool
     */
    public function hasStack(): bool
    {
        return !is_null($this->stack) && $this->stack !== "\n";
    }

    /**
     * Check if the entry has a context.
     *
     * @return bool
     */
    public function hasContext(): bool
    {
        return !empty($this->context);
    }

    /**
     * Check if same log level.
     *
     * @param  string  $level
     *
     * @return bool
     */
    public function isSameLevel($level): bool
    {
        return $this->level === $level;
    }

    /**
     * Check if similar to.
     *
     * @param  string  $text
     * @param  float  $similarity
     *
     * @return bool
     */
    public function isSimilar(string $text, float $similarity): bool
    {
        $percent = 0;

        similar_text($text, $this->header, $percent);

        return $percent >= $similarity;
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Extract datetime from the header.
     *
     * @param  array  $header
     *
     * @return array
     */
    protected function extractDatetime(array &$header): array
    {
        $separator = ($header[2] ?? null) === 'T' ? '\T' : ' ';
        $ms = ($header[3] ?? null) ? '.u' : '';
        $tz = ($header[4] ?? null) ? 'P' : '';
        $format = "Y-m-d{$separator}H:i:s{$ms}{$tz}";
        $datetime = $header[1];

        return [$format, $datetime];
    }
}
