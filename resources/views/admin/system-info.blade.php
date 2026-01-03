@extends('layouts.app')

@section('title', 'System Information - Admin Panel')

@section('content')
<div class="admin-page">
    <div class="page-header">
        <h1>System Information</h1>
        <div class="header-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    
    <div class="system-info-grid">
        
        <div class="info-card">
            <div class="card-header">
                <i class="fab fa-php"></i>
                <h3>PHP Information</h3>
            </div>
            <div class="card-content">
                <div class="info-item">
                    <span class="label">Version:</span>
                    <span class="value">{{ $info['php_version'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Memory Limit:</span>
                    <span class="value">{{ ini_get('memory_limit') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Max Execution Time:</span>
                    <span class="value">{{ ini_get('max_execution_time') }}s</span>
                </div>
                <div class="info-item">
                    <span class="label">Upload Max Size:</span>
                    <span class="value">{{ ini_get('upload_max_filesize') }}</span>
                </div>
            </div>
        </div>

        
        <div class="info-card">
            <div class="card-header">
                <i class="fab fa-laravel"></i>
                <h3>Laravel Framework</h3>
            </div>
            <div class="card-content">
                <div class="info-item">
                    <span class="label">Version:</span>
                    <span class="value">{{ $info['laravel_version'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Environment:</span>
                    <span class="value">{{ config('app.env') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Debug Mode:</span>
                    <span class="value {{ $info['debug_mode'] === 'Enabled' ? 'status-enabled' : 'status-disabled' }}">
                        {{ $info['debug_mode'] }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="label">Maintenance Mode:</span>
                    <span class="value {{ $info['maintenance_mode'] === 'Enabled' ? 'status-enabled' : 'status-disabled' }}">
                        {{ $info['maintenance_mode'] }}
                    </span>
                </div>
            </div>
        </div>

        
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-database"></i>
                <h3>Database</h3>
            </div>
            <div class="card-content">
                <div class="info-item">
                    <span class="label">Connection:</span>
                    <span class="value">{{ $info['database'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Host:</span>
                    <span class="value">{{ config('database.connections.' . config('database.default') . '.host') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Database:</span>
                    <span class="value">{{ config('database.connections.' . config('database.default') . '.database') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Driver:</span>
                    <span class="value">{{ config('database.connections.' . config('database.default') . '.driver') }}</span>
                </div>
            </div>
        </div>

        
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-memory"></i>
                <h3>Caching</h3>
            </div>
            <div class="card-content">
                <div class="info-item">
                    <span class="label">Cache Driver:</span>
                    <span class="value">{{ $info['cache_driver'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Session Driver:</span>
                    <span class="value">{{ $info['session_driver'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Queue Driver:</span>
                    <span class="value">{{ $info['queue_driver'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Mail Driver:</span>
                    <span class="value">{{ $info['mail_driver'] }}</span>
                </div>
            </div>
        </div>

        
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-folder"></i>
                <h3>File System</h3>
            </div>
            <div class="card-content">
                <div class="info-item">
                    <span class="label">Storage Path:</span>
                    <span class="value">{{ $info['storage_path'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Public Path:</span>
                    <span class="value">{{ $info['public_path'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Storage Disk:</span>
                    <span class="value">Local</span>
                </div>
                <div class="info-item">
                    <span class="label">File Permissions:</span>
                    <span class="value {{ is_writable(storage_path()) ? 'status-enabled' : 'status-disabled' }}">
                        {{ is_writable(storage_path()) ? 'Writable' : 'Read-only' }}
                    </span>
                </div>
            </div>
        </div>

        
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-cog"></i>
                <h3>Application</h3>
            </div>
            <div class="card-content">
                <div class="info-item">
                    <span class="label">App URL:</span>
                    <span class="value">{{ $info['app_url'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Timezone:</span>
                    <span class="value">{{ $info['timezone'] }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Locale:</span>
                    <span class="value">{{ config('app.locale') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Fallback Locale:</span>
                    <span class="value">{{ config('app.fallback_locale') }}</span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="server-info-section">
        <h2>Server Information</h2>
        <div class="server-details">
            <div class="detail-item">
                <span class="label">Server Software:</span>
                <span class="value">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Server Name:</span>
                <span class="value">{{ $_SERVER['SERVER_NAME'] ?? 'Unknown' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Server Address:</span>
                <span class="value">{{ $_SERVER['SERVER_ADDR'] ?? 'Unknown' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Server Port:</span>
                <span class="value">{{ $_SERVER['SERVER_PORT'] ?? 'Unknown' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Remote Address:</span>
                <span class="value">{{ $_SERVER['REMOTE_ADDR'] ?? 'Unknown' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Request Time:</span>
                <span class="value">{{ date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()) }}</span>
            </div>
        </div>
    </div>

    
    <div class="extensions-section">
        <h2>PHP Extensions</h2>
        <div class="extensions-grid">
            @php
                $extensions = get_loaded_extensions();
                sort($extensions);
            @endphp
            @foreach(array_chunk($extensions, ceil(count($extensions) / 3)) as $chunk)
            <div class="extensions-column">
                @foreach($chunk as $extension)
                <span class="extension-tag">{{ $extension }}</span>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
.admin-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.page-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.header-actions {
    display: flex;
    gap: 12px;
}

/* System Info Grid */
.system-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.info-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.card-header {
    background: var(--twitter-light);
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-header i {
    font-size: 24px;
    color: var(--twitter-blue);
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.card-content {
    padding: 20px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.label {
    font-weight: 500;
    color: var(--twitter-gray);
    font-size: 14px;
}

.value {
    font-weight: 600;
    color: var(--twitter-dark);
    font-size: 14px;
    text-align: right;
}

.status-enabled {
    color: #28a745 !important;
}

.status-disabled {
    color: #dc3545 !important;
}

/* Server Information */
.server-info-section {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}

.server-info-section h2 {
    margin: 0 0 20px 0;
    font-size: 22px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.server-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item .label {
    font-weight: 500;
    color: var(--twitter-gray);
}

.detail-item .value {
    font-family: monospace;
    font-size: 13px;
    color: var(--twitter-dark);
    background: var(--twitter-light);
    padding: 4px 8px;
    border-radius: 4px;
}

/* PHP Extensions */
.extensions-section {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
}

.extensions-section h2 {
    margin: 0 0 20px 0;
    font-size: 22px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.extensions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.extensions-column {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.extension-tag {
    background: var(--twitter-light);
    color: var(--twitter-dark);
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    border: 1px solid var(--border-color);
}

.btn-secondary {
    background: var(--card-bg);
    color: var(--twitter-gray);
    border: 2px solid var(--border-color);
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-secondary:hover {
    background: var(--hover-bg);
    border-color: var(--twitter-blue);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .system-info-grid {
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    }

    .extensions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .admin-page {
        padding: 16px;
    }

    .page-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }

    .system-info-grid {
        grid-template-columns: 1fr;
    }

    .server-details {
        grid-template-columns: 1fr;
    }

    .extensions-grid {
        grid-template-columns: 1fr;
    }

    .info-item,
    .detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .value {
        text-align: left;
    }
}

@media (max-width: 480px) {
    .card-content {
        padding: 16px;
    }

    .server-info-section,
    .extensions-section {
        padding: 16px;
    }

    .extension-tag {
        font-size: 11px;
        padding: 4px 8px;
    }
}
</style>
@endsection
