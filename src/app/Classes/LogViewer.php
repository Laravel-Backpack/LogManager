<?php

namespace Backpack\LogManager\app\Classes;

use Illuminate\Support\Facades\Storage;

/**
 * Class LogViewer.
 */
class LogViewer
{
    /**
     * @var string file
     */
    private static $file;

    /**
     * Map debug levels to Bootstrap classes.
     *
     * @var array
     */
    private static $levels_classes = [
        'debug'     => 'info',
        'info'      => 'info',
        'notice'    => 'info',
        'warning'   => 'warning',
        'error'     => 'danger',
        'critical'  => 'danger',
        'alert'     => 'danger',
        'emergency' => 'danger',
        'processed' => 'info',
    ];

    /**
     * Map debug levels to icon classes.
     *
     * @var array
     */
    private static $levels_imgs = [
        'debug'     => 'info',
        'info'      => 'info',
        'notice'    => 'info',
        'warning'   => 'warning',
        'error'     => 'warning',
        'critical'  => 'warning',
        'alert'     => 'warning',
        'emergency' => 'warning',
        'processed' => 'info',
    ];

    /**
     * Log levels that are used.
     *
     * @var array
     */
    private static $log_levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
        'processed',
    ];

    /**
     * Arbitrary max file size.
     */
    const MAX_FILE_SIZE = 52428800;

    /**
     * @param string $file
     *
     * @throws \Exception
     */
    public static function setFile($file)
    {
        $file = static::pathToLogFile($file);

        if (app('files')->exists($file)) {
            static::$file = $file;
        }
    }

    /**
     * @param string $file
     *
     * @throws
     *
     * @return string
     */
    public static function pathToLogFile($file)
    {
        $logsPath = storage_path('logs');

        if (app('files')->exists($file)) { // try the absolute path
            return $file;
        }

        $file = $logsPath.'/'.$file;

        // check if requested file is really in the logs directory
        if (dirname($file) !== $logsPath) {
            throw new \Exception('No such log file');
        }

        return $file;
    }

    /**
     * @return string
     */
    public static function getFileName()
    {
        return basename(static::$file);
    }

    /**
     * @throws \Illuminate\Container\EntryNotFoundException
     *
     * @return array
     */
    public static function all()
    {
        $log = [];

        if (!static::$file) {
            $log_file = static::getFiles();
            if (!count($log_file)) {
                return [];
            }
            static::$file = $log_file[0];
        }

        if (app('files')->size(static::$file) > static::MAX_FILE_SIZE) {
            return [];
        }

        $file = app('files')->get(static::$file);

        $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/';

        preg_match_all($pattern, $file, $headings);

        if (!is_array($headings)) {
            return $log;
        }

        $stack_trace = preg_split($pattern, $file);

        if ($stack_trace[0] < 1) {
            array_shift($stack_trace);
        }

        foreach ($headings as $h) {
            for ($i = 0, $j = count($h); $i < $j; $i++) {
                foreach (static::$log_levels as $level) {
                    if (strpos(strtolower($h[$i]), '.'.$level) || strpos(strtolower($h[$i]), $level.':')) {
                        $pattern = '/^\[(?P<date>(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}))\](?:.*?(?P<context>(\w+))\.|.*?)'.$level.': (?P<text>.*?)(?P<in_file> in .*?:[0-9]+)?$/i';
                        preg_match($pattern, $h[$i], $current);
                        if (!isset($current['text'])) {
                            continue;
                        }

                        $log[] = [
                            'context'     => $current['context'],
                            'level'       => $level,
                            'level_class' => static::$levels_classes[$level],
                            'level_img'   => static::$levels_imgs[$level],
                            'date'        => $current['date'],
                            'text'        => $current['text'],
                            'in_file'     => isset($current['in_file']) ? $current['in_file'] : null,
                            'stack'       => preg_replace("/^\n*/", '', $stack_trace[$i]),
                        ];
                    }
                }
            }
        }

        if (empty($log)) {
            $lines = preg_split('/\r\n|\r|\n/', $file);

            foreach ($lines as $line) {
                if (!empty(trim($line))) {
                    $log[] = [
                        'context'     => null,
                        'level'       => 'info',
                        'level_class' => static::$levels_classes['info'],
                        'level_img'   => static::$levels_imgs['info'],
                        'date'        => null,
                        'text'        => $line,
                        'in_file'     => null,
                        'stack'       => '',
                    ];
                }
            }
        }

        return array_reverse($log);
    }

    /**
     * @param bool $basename
     * @param bool $sort_by_date Most recently modified files first
     *
     * @return array
     */
    public static function getFiles($basename = false, $sort_by_date = false)
    {
        $files = glob(storage_path().'/logs/*.log');
        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');

        if ($basename && is_array($files)) {
            foreach ($files as $k => $file) {
                $disk = Storage::disk(config('backpack.base.root_disk_name'));
                $file_name = basename($file);

                if ($disk->exists('storage/logs/'.$file_name)) {
                    $files[$k] = [
                        'file_name'     => $file_name,
                        'file_size'     => $disk->size('storage/logs/'.$file_name),
                        'last_modified' => $disk->lastModified('storage/logs/'.$file_name),
                    ];
                }
            }

            if ($sort_by_date) {
                usort($files, function ($a, $b) {
                    return $b['last_modified'] - $a['last_modified'];
                });
            }
        }

        return array_values($files);
    }
}
