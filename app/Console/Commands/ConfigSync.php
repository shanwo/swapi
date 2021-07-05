<?php
/**
 * User: Powerless
 * Date: 21/07/05
 * Blog: https://wzxaini9.cn
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;

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
        $result = [
            'data'    => [],
            'code'    => 200,
            'msg'     => '定时任务测试'.env('APP_ENV'),
            'uuid'    => Uuid::uuid6(),
        ];
        $this->redis::SETEX('console:'.date('Y-m-d h:i:s'), 600, json_encode($result));
    }
}
