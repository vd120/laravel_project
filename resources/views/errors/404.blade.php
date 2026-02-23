@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="error-page">
    <div class="error-content">
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">The page you're looking for doesn't exist or has been moved.</p>
        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <i class="fas fa-home"></i> Go Home
            </a>
            <button onclick="history.back()" class="btn">
                <i class="fas fa-arrow-left"></i> Go Back
            </button>
        </div>
    </div>
</div>

<style>
.error-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    padding: 40px 20px;
}

.error-content {
    text-align: center;
    max-width: 500px;
}

.error-code {
    font-size: 8rem;
    font-weight: 900;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1;
    margin-bottom: 16px;
}

.error-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 12px 0;
}

.error-message {
    font-size: 1rem;
    color: var(--text-muted);
    margin: 0 0 32px 0;
    line-height: 1.6;
}

.error-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 480px) {
    .error-code {
        font-size: 5rem;
    }
    
    .error-title {
        font-size: 1.5rem;
    }
    
    .error-actions {
        flex-direction: column;
    }
    
    .error-actions .btn {
        width: 100%;
    }
}
</style>
@endsection