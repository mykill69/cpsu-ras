<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ToggleMaintenanceMode extends Command
{
    protected $signature = 'maintenance:toggle {mode : down or up}';
    protected $description = 'Toggle maintenance mode on or off';

    public function handle()
    {
        $mode = $this->argument('mode');
        $downFile = storage_path('framework/down');

        if ($mode === 'down') {
            // Enable maintenance mode
            $dir = dirname($downFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $payload = json_encode([
                'time' => time(),
                'message' => 'System is under maintenance. Please check back later.',
                'retry' => 60,
            ]);
            
            file_put_contents($downFile, $payload);
            $this->info('Maintenance mode enabled');
        } elseif ($mode === 'up') {
            // Disable maintenance mode
            if (file_exists($downFile)) {
                unlink($downFile);
            }
            $this->info('Maintenance mode disabled');
        } else {
            $this->error('Invalid mode. Use "down" or "up"');
            return 1;
        }

        return 0;
    }
}