<?php
/**
 * User: Powerless
 * Date: 21/07/05
 * Blog: https://wzxaini9.cn
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ConfigSync extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:config_sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Config_Sync';

    protected $redis;

    /**
     * php artisan command:config_sync
     * ConfigSync constructor.
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        parent::__construct();
        $this->redis = $redis;
    }

    public function handle()
    {
        echo 111;
    }
}
