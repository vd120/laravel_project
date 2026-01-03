@extends('layouts.app')

@section('title', 'Account Suspended')

@section('content')
<div class="suspended-page">
    <div class="suspended-container">
        <div class="suspended-header">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1>Account Suspended</h1>
            <p class="suspended-subtitle">Your account has been suspended by an administrator</p>
        </div>

        <div class="suspended-content">
            <div class="suspended-actions">
                <div class="action-buttons">
                    <a href="{{ route('login') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.suspended-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.suspended-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    max-width: 500px;
    width: 100%;
    overflow: hidden;
}

.suspended-header {
    text-align: center;
    padding: 40px 30px 30px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
}

.warning-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.9;
}

.suspended-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
    font-weight: 700;
}

.suspended-subtitle {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
    font-weight: 300;
}

.suspended-content {
    padding: 30px;
}

.suspended-message {
    margin-bottom: 30px;
}

.suspended-message h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 18px;
    font-weight: 600;
}

.suspended-message ul {
    margin: 0;
    padding-left: 20px;
}

.suspended-message li {
    margin-bottom: 8px;
    color: #666;
    line-height: 1.5;
}

.suspended-actions {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.suspended-actions p {
    margin: 0 0 20px 0;
    color: #666;
    font-size: 14px;
}

.action-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary {
    background: #dc3545;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 25px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn-primary:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
}

.btn-secondary {
    background: white;
    color: #666;
    border: 2px solid #ddd;
    padding: 12px 20px;
    border-radius: 25px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #f8f9fa;
    border-color: #bbb;
    transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 480px) {
    .suspended-page {
        padding: 10px;
    }

    .suspended-container {
        border-radius: 15px;
    }

    .suspended-header {
        padding: 30px 20px 20px;
    }

    .warning-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .suspended-header h1 {
        font-size: 24px;
    }

    .suspended-content {
        padding: 20px;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn-primary,
    .btn-secondary {
        justify-content: center;
    }
}
</style>
@endsection
