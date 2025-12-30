<!-- Common Loader Component -->
<div id="page-loader" class="page-loader">
    <div class="loader-content">
        <div class="loader-spinner-wrapper">
            <div class="loader-spinner"></div>
            <div class="loader-logo">
                @php($store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first())
                <img src="{{ \App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value ?? '', $store_logo?->storage[0]?->value ?? 'public', 'favicon') }}" 
                     alt="Logo" 
                     class="loader-logo-img"
                     onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'">
            </div>
        </div>
        <div class="loader-text">Loading...</div>
    </div>
</div>

<style>
    /* Page Loader Styles */
    .page-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease, background 0.2s ease;
        pointer-events: none;
    }
    
    .page-loader:not(.hide) {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        background: rgba(0, 0, 0, 0.5);
    }

    .page-loader.hide {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        background: transparent;
    }

    .loader-content {
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .loader-spinner-wrapper {
        position: relative;
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    .loader-spinner {
        position: absolute;
        top: 0;
        left: 0;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid transparent;
        border-top-color: #ff6b35;
        border-right-color: #ff6b35;
        border-bottom-color: #4caf50;
        border-left-color: #4caf50;
        animation: spinnerRotate 1.2s linear infinite;
        z-index: 1;
    }

    .loader-logo {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90px;
        height: 90px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .loader-logo-img {
        max-width: 75px;
        max-height: 75px;
        object-fit: contain;
        border-radius: 50%;
    }

    @keyframes spinnerRotate {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }


    .loader-text {
        font-size: 14px;
        font-weight: 600;
        color: #5e6278;
        margin-top: 20px;
        text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
        position: relative;
        text-align: center;
        white-space: nowrap;
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .page-loader:not(.hide) {
            background: rgba(0, 0, 0, 0.7);
        }
        .loader-text {
            color: #a1a5b7;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }
        .loader-logo-img {
            background: #1e1e1e;
        }
    }
</style>

<script>
    // Common Loader Functions
    window.PageLoader = {
        show: function() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.classList.remove('hide');
            }
        },
        
        hide: function() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.classList.add('hide');
            }
        },
        
        toggle: function() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.classList.toggle('hide');
            }
        }
    };

    // Show loader on page unload (for navigation)
    // But prevent it from showing on tab navigation within the same page
    window.addEventListener('beforeunload', function() {
        // Check if we should prevent loader (for tab navigation)
        if (!window._preventLoaderOnNavigation) {
            PageLoader.show();
        } else {
            // Reset the flag after a short delay
            setTimeout(function() {
                window._preventLoaderOnNavigation = false;
            }, 100);
        }
    });
</script>

