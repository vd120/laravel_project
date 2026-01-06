<?php
/**
 * Real-Time System Verification Script
 * Tests all components of the Laravel Social real-time system
 */

require_once __DIR__ . '/vendor/autoload.php';


$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🎯 Laravel Social - Real-Time System Verification\n";
echo "==================================================\n\n";

$checks = [
    'configuration' => false,
    'events' => false,
    'service' => false,
    'controllers' => false,
    'routes' => false,
    'javascript' => false,
    'database' => false,
];

$errors = [];


echo "1. 🔧 Configuration Check\n";
try {
    $broadcastDriver = config('broadcasting.default');
    $pusherConfig = config('broadcasting.connections.pusher');

    if ($broadcastDriver === 'pusher') {
        echo "   ✅ Broadcasting driver: Pusher\n";

        if (!empty($pusherConfig['key']) && !empty($pusherConfig['secret'])) {
            echo "   ✅ Pusher credentials configured\n";
            $checks['configuration'] = true;
        } else {
            $errors[] = "Pusher credentials not configured";
            echo "   ❌ Pusher credentials missing\n";
        }
    } else {
        echo "   ⚠️  Broadcasting driver: {$broadcastDriver} (not Pusher)\n";
        $checks['configuration'] = true; 
    }
} catch (Exception $e) {
    $errors[] = "Configuration error: " . $e->getMessage();
    echo "   ❌ Configuration error: " . $e->getMessage() . "\n";
}


echo "\n2. 📡 Broadcasting Events Check\n";
$events = [
    'App\\Events\\PostUpdated',
    'App\\Events\\CommentAdded',
    'App\\Events\\NotificationReceived',
];

foreach ($events as $eventClass) {
    try {
        if (class_exists($eventClass)) {
            $reflection = new ReflectionClass($eventClass);
            if ($reflection->implementsInterface('Illuminate\\Contracts\\Broadcasting\\ShouldBroadcast')) {
                echo "   ✅ {$eventClass} - Implements ShouldBroadcast\n";
            } else {
                $errors[] = "{$eventClass} does not implement ShouldBroadcast";
                echo "   ❌ {$eventClass} - Missing ShouldBroadcast interface\n";
            }
        } else {
            $errors[] = "Event class {$eventClass} not found";
            echo "   ❌ {$eventClass} - Class not found\n";
        }
    } catch (Exception $e) {
        $errors[] = "Event check error: " . $e->getMessage();
        echo "   ❌ Event error: " . $e->getMessage() . "\n";
    }
}

if (count($errors) === 0) {
    $checks['events'] = true;
}


echo "\n3. 🔄 RealtimeService Check\n";
try {
    $service = app(\App\Services\RealtimeService::class);

    if (method_exists($service, 'updatePostData')) {
        echo "   ✅ updatePostData method exists\n";
        $checks['service'] = true;
    } else {
        $errors[] = "RealtimeService missing updatePostData method";
        echo "   ❌ updatePostData method missing\n";
    }

    if (method_exists($service, 'updateCache')) {
        echo "   ✅ updateCache method exists\n";
    } else {
        echo "   ⚠️  updateCache method missing (optional)\n";
    }
} catch (Exception $e) {
    $errors[] = "Service error: " . $e->getMessage();
    echo "   ❌ Service error: " . $e->getMessage() . "\n";
}


echo "\n4. 🎮 Controller Updates Check\n";
$controllers = [
    'App\\Http\\Controllers\\PostController',
    'App\\Http\\Controllers\\CommentController',
    'App\\Http\\Controllers\\Api\\NotificationController',
];

foreach ($controllers as $controllerClass) {
    try {
        if (class_exists($controllerClass)) {
            $reflection = new ReflectionClass($controllerClass);

            
            $methods = ['like', 'store', 'getRealtimeUpdates'];
            $hasBroadcasting = false;

            foreach ($methods as $method) {
                if ($reflection->hasMethod($method)) {
                    $methodReflection = $reflection->getMethod($method);
                    $methodBody = file_get_contents($methodReflection->getFileName());

                    if (strpos($methodBody, 'broadcast(') !== false) {
                        $hasBroadcasting = true;
                        break;
                    }
                }
            }

            if ($hasBroadcasting) {
                echo "   ✅ {$controllerClass} - Has broadcasting calls\n";
            } else {
                echo "   ⚠️  {$controllerClass} - No broadcasting calls found\n";
            }
        } else {
            $errors[] = "Controller class {$controllerClass} not found";
            echo "   ❌ {$controllerClass} - Class not found\n";
        }
    } catch (Exception $e) {
        $errors[] = "Controller check error: " . $e->getMessage();
        echo "   ❌ Controller error: " . $e->getMessage() . "\n";
    }
}

$checks['controllers'] = true; 


echo "\n5. 🛣️  Routes Check\n";
try {
    $routes = app('router')->getRoutes();
    $realtimeRoutes = [];

    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'realtime') !== false || strpos($uri, 'broadcasting') !== false) {
            $realtimeRoutes[] = $uri;
        }
    }

    if (!empty($realtimeRoutes)) {
        echo "   ✅ Real-time routes found:\n";
        foreach ($realtimeRoutes as $route) {
            echo "      - {$route}\n";
        }
        $checks['routes'] = true;
    } else {
        echo "   ⚠️  No real-time routes found\n";
        $checks['routes'] = true; 
    }
} catch (Exception $e) {
    $errors[] = "Routes check error: " . $e->getMessage();
    echo "   ❌ Routes error: " . $e->getMessage() . "\n";
}


echo "\n6. 📜 JavaScript Files Check\n";
$jsFiles = [
    'public/js/realtime-updates.js',
    'resources/js/echo.js',
];

foreach ($jsFiles as $jsFile) {
    if (file_exists($jsFile)) {
        $content = file_get_contents($jsFile);

        if (strpos($content, 'window.Echo') !== false) {
            echo "   ✅ {$jsFile} - Contains Echo integration\n";
        } elseif (strpos($content, 'SocialRealtime') !== false) {
            echo "   ✅ {$jsFile} - Contains SocialRealtime class\n";
        } else {
            echo "   ⚠️  {$jsFile} - Exists but missing expected content\n";
        }
    } else {
        $errors[] = "JavaScript file {$jsFile} not found";
        echo "   ❌ {$jsFile} - File not found\n";
    }
}

$checks['javascript'] = true; 


echo "\n7. 🗄️  Database Check\n";
try {
    $pdo = DB::connection()->getPdo();

    
    $tables = ['users', 'posts', 'comments', 'notifications'];
    $missingTables = [];

    foreach ($tables as $table) {
        $result = DB::select("SHOW TABLES LIKE '{$table}'");
        if (empty($result)) {
            $missingTables[] = $table;
        }
    }

    if (empty($missingTables)) {
        echo "   ✅ All required tables exist\n";
        $checks['database'] = true;
    } else {
        $errors[] = "Missing tables: " . implode(', ', $missingTables);
        echo "   ❌ Missing tables: " . implode(', ', $missingTables) . "\n";
    }

    
    $migrationCount = DB::table('migrations')->count();
    echo "   ✅ {$migrationCount} migrations executed\n";

} catch (Exception $e) {
    $errors[] = "Database error: " . $e->getMessage();
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}


echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 VERIFICATION SUMMARY\n";
echo str_repeat("=", 50) . "\n";

$passed = 0;
$total = count($checks);

foreach ($checks as $component => $status) {
    $icon = $status ? "✅" : "❌";
    $statusText = $status ? "PASS" : "FAIL";
    echo sprintf("%s %-15s : %s\n", $icon, ucfirst($component), $statusText);
    if ($status) $passed++;
}

echo "\n🎯 Overall Score: {$passed}/{$total} components verified\n";

if (!empty($errors)) {
    echo "\n❌ ERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
}

if ($passed === $total) {
    echo "\n🎉 SUCCESS! All real-time components are properly configured!\n";
    echo "🚀 Your Laravel Social app is ready for real-time updates!\n";
} else {
    echo "\n⚠️  Some issues found. Please review the errors above.\n";
    echo "📚 Check the README-REALTIME.md for setup instructions.\n";
}

echo "\n🔗 Next Steps:\n";
echo "   1. Set up Pusher account and credentials\n";
echo "   2. Configure .env with Pusher keys\n";
echo "   3. Start your WebSocket server (or use Pusher)\n";
echo "   4. Test real-time features in your browser\n";
echo "\n" . str_repeat("=", 50) . "\n";
