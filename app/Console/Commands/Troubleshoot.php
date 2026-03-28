<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class Troubleshoot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nexus:troubleshoot {--fix : Automatically fix issues} {--verbose : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactive troubleshooting wizard for Nexus';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Nexus - Interactive Troubleshooting Wizard');
        $this->newLine();

        $issues = [];
        $fixable = 0;

        // Check 1: PHP Version
        $this->check('Checking PHP version...', function () use (&$issues, &$fixable) {
            $version = PHP_VERSION;
            $major = PHP_MAJOR_VERSION;
            $minor = PHP_MINOR_VERSION;

            if ($major < 8 || ($major === 8 && $minor < 2)) {
                $issues[] = [
                    'type' => 'error',
                    'message' => "PHP version is {$version}. Required: 8.2+",
                    'fix' => 'Upgrade PHP to 8.2 or higher',
                    'fixable' => false
                ];
                return false;
            }
            $this->output->write("  OK PHP {$version}");
            return true;
        });

        // Check 2: Required Extensions
        $this->check('Checking PHP extensions...', function () use (&$issues, &$fixable) {
            $required = ['mbstring', 'xml', 'curl', 'zip', 'openssl', 'pdo', 'json', 'tokenizer', 'bcmath', 'mysql'];
            $missing = [];

            foreach ($required as $ext) {
                if (!extension_loaded($ext)) {
                    $missing[] = $ext;
                }
            }

            if (!empty($missing)) {
                $issues[] = [
                    'type' => 'error',
                    'message' => "Missing extensions: " . implode(', ', $missing),
                    'fix' => 'Install missing PHP extensions',
                    'fixable' => true
                ];
                $fixable++;
                return false;
            }
            $this->output->writeln('  OK All required extensions loaded');
            return true;
        });

        // Check 3: .env File
        $this->check('Checking .env file...', function () use (&$issues, &$fixable) {
            if (!File::exists(base_path('.env'))) {
                $issues[] = [
                    'type' => 'error',
                    'message' => '.env file not found',
                    'fix' => 'Copy .env.example to .env',
                    'fixable' => true
                ];
                $fixable++;
                return false;
            }
            $this->output->writeln('  OK .env file exists');
            return true;
        });

        // Check 4: Application Key
        $this->check('Checking application key...', function () use (&$issues, &$fixable) {
            if (empty(config('app.key'))) {
                $issues[] = [
                    'type' => 'error',
                    'message' => 'Application key not set',
                    'fix' => 'Run php artisan key:generate',
                    'fixable' => true
                ];
                $fixable++;
                return false;
            }
            $this->output->writeln('  OK Application key set');
            return true;
        });

        // Check 5: Database Connection
        $this->check('Checking database connection...', function () use (&$issues, &$fixable) {
            try {
                DB::connection()->getPdo();
                $this->output->writeln('  OK Database connection successful');
                return true;
            } catch (\Exception $e) {
                $issues[] = [
                    'type' => 'error',
                    'message' => 'Cannot connect to database: ' . $e->getMessage(),
                    'fix' => 'Check database credentials in .env',
                    'fixable' => false
                ];
                return false;
            }
        });

        // Check 6: Storage Directory
        $this->check('Checking storage directory...', function () use (&$issues, &$fixable) {
            $dirs = [
                storage_path(),
                storage_path('app/public'),
                storage_path('logs'),
                storage_path('framework/cache'),
                storage_path('framework/sessions'),
                storage_path('framework/views')
            ];

            $missing = [];
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) {
                    $missing[] = $dir;
                }
            }

            if (!empty($missing)) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => 'Some storage directories missing',
                    'fix' => 'Run php artisan storage:link and check permissions',
                    'fixable' => true
                ];
                $fixable++;
                return false;
            }
            $this->output->writeln('  OK Storage directories exist');
            return true;
        });

        // Check 7: Storage Link
        $this->check('Checking storage symbolic link...', function () use (&$issues, &$fixable) {
            if (!is_link(public_path('storage'))) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => 'Storage symbolic link not created',
                    'fix' => 'Run php artisan storage:link',
                    'fixable' => true
                ];
                $fixable++;
                return false;
            }
            $this->output->writeln('  OK Storage link exists');
            return true;
        });

        // Check 8: Bootstrap Cache
        $this->check('Checking bootstrap cache...', function () use (&$issues, &$fixable) {
            if (!is_dir(base_path('bootstrap/cache'))) {
                $issues[] = [
                    'type' => 'error',
                    'message' => 'Bootstrap cache directory missing',
                    'fix' => 'Create bootstrap/cache directory',
                    'fixable' => true
                ];
                $fixable++;
                return false;
            }
            $this->output->writeln('  OK Bootstrap cache directory exists');
            return true;
        });

        // Check 9: Composer Dependencies
        $this->check('Checking composer dependencies...', function () use (&$issues, &$fixable) {
            if (!File::exists(base_path('vendor/autoload.php'))) {
                $issues[] = [
                    'type' => 'error',
                    'message' => 'Composer dependencies not installed',
                    'fix' => 'Run composer install',
                    'fixable' => true
                ];
                $fixable++;
                return false;
            }
            $this->output->writeln('  OK Composer dependencies installed');
            return true;
        });

        // Check 10: Node Modules
        $this->check('Checking node modules...', function () use (&$issues, &$fixable) {
            if (!File::exists(base_path('node_modules'))) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => 'Node modules not installed',
                    'fix' => 'Run npm install',
                    'fixable' => true
                ];
                $fixable++;
                return false;
            }
            $this->output->writeln('  OK Node modules installed');
            return true;
        });

        // Summary
        $this->newLine();
        $this->info('Summary');
        $this->info('-------');

        if (empty($issues)) {
            $this->output->writeln('All checks passed!');
            $this->newLine();
            return 0;
        }

        $this->output->writeln("Found {$fixable} fixable issue(s)");
        $this->newLine();

        foreach ($issues as $i => $issue) {
            $icon = $issue['type'] === 'error' ? 'X' : '!';
            $this->output->writeln("{$icon} Issue #" . ($i + 1) . ": {$issue['message']}");
            $this->output->writeln("   Fix: {$issue['fix']}");
            $this->newLine();
        }

        // Auto-fix option
        if ($this->option('fix') && $fixable > 0) {
            $this->newLine();
            if ($this->confirm('Would you like to attempt to fix these issues automatically?')) {
                $this->fixIssues($issues);
            }
        } elseif ($fixable > 0) {
            $this->newLine();
            $this->info('Run with --fix flag to attempt automatic fixes:');
            $this->comment('php artisan nexus:troubleshoot --fix');
        }

        if ($this->option('verbose')) {
            $this->newLine();
            $this->info('Verbose Information:');
            $this->table(
                ['Config', 'Value'],
                [
                    ['PHP Version', PHP_VERSION],
                    ['PHP Extensions', implode(', ', get_loaded_extensions())],
                    ['App Key', config('app.key') ? 'Set' : 'Not Set'],
                    ['DB Connection', config('database.default')],
                    ['App URL', config('app.url')],
                    ['Debug Mode', config('app.debug') ? 'Enabled' : 'Disabled'],
                ]
            );
        }

        return empty($issues) ? 0 : 1;
    }

    /**
     * Run a check and output result
     */
    private function check($message, $callback)
    {
        $this->output->write("{$message}...");
        $callback();
    }

    /**
     * Attempt to fix issues
     */
    private function fixIssues($issues)
    {
        foreach ($issues as $issue) {
            if (!$issue['fixable']) {
                continue;
            }

            if (str_contains($issue['fix'], 'php artisan key:generate')) {
                $this->call('key:generate');
                $this->info('✓ Application key generated');
            }

            if (str_contains($issue['fix'], 'php artisan storage:link')) {
                $this->call('storage:link');
                $this->info('✓ Storage link created');
            }

            if (str_contains($issue['fix'], 'composer install')) {
                $this->info('Running composer install...');
                exec('composer install --no-interaction --prefer-dist --optimize-autoloader', $output, $status);
                if ($status === 0) {
                    $this->info('✓ Composer dependencies installed');
                }
            }

            if (str_contains($issue['fix'], 'npm install')) {
                $this->info('Running npm install...');
                exec('npm install', $output, $status);
                if ($status === 0) {
                    $this->info('✓ Node modules installed');
                }
            }
        }

        $this->newLine();
        $this->info('Re-running checks...');
        $this->newLine();
        $this->call('nexus:troubleshoot');
    }
}
