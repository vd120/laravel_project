@extends('layouts.app')

@section('content')
<div class="error-page">
    <div class="error-content">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1 class="error-title">404</h1>
        <h2 class="error-subtitle">Page Not Found</h2>
        <p class="error-message">
            Sorry, the page you are looking for could not be found.
        </p>
        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
            <button onclick="history.back()" class="btn btn-secondary">Go Back</button>
        </div>
    </div>
</div>

<style>
.error-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 70vh;
    padding: 20px;
}

.error-content {
    text-align: center;
    max-width: 500px;
}

.error-icon {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.error-title {
    font-size: 6rem;
    font-weight: 700;
    color: var(--twitter-dark);
    margin: 0;
    line-height: 1;
}

.error-subtitle {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--twitter-dark);
    margin: 1rem 0;
}

.error-message {
    font-size: 1rem;
    color: var(--twitter-gray);
    margin-bottom: 2rem;
    line-height: 1.5;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
}

.btn-primary {
    background: var(--twitter-blue);
    color: white;
}

.btn-primary:hover {
    background: #1a91da;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

@media (max-width: 480px) {
    .error-title {
        font-size: 4rem;
    }

    .error-subtitle {
        font-size: 1.25rem;
    }

    .error-actions {
        flex-direction: column;
        align-items: center;
    }

    .btn {
        width: 100%;
        max-width: 200px;
    }
}
</style>
@endsection
