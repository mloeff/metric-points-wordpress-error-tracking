/**
 * Metric Points Error Tracker - Comprehensive Error Tracking & Session Replay
 * Version: 2.0.0
 * 
 * Features:
 * - JavaScript error capture (window.onerror, unhandledrejection)
 * - Session replay (mouse movements, clicks, scroll, keypresses)
 * - Performance metrics (timing, memory, CPU)
 * - User context (browser, device, location)
 * - Page context (DOM elements, form data, console logs)
 * - Network monitoring (requests, responses)
 * - Storage monitoring (localStorage, sessionStorage, cookies)
 */

(function() {
    'use strict';

    // Configuration
    let config = {
        apiKey: null,
        endpoint: null,
        sampleRate: 1.0,
        debug: false,
        ignorePatterns: [],
        sessionReplay: {
            enabled: true,
            maxEvents: 1000,
            mouseTracking: true,
            clickTracking: true,
            scrollTracking: true,
            keypressTracking: true,
            focusTracking: true,
            urlTracking: true
        },
        performance: {
            enabled: true,
            captureMetrics: true,
            memoryMonitoring: true
        },
        privacy: {
            maskUserInput: true,
            excludeSensitiveFields: ['password', 'credit-card', 'ssn'],
            userConsent: false
        },
        metadata: {}
    };

    // Session replay data storage
    let sessionData = {
        sessionId: generateSessionId(),
        startTime: Date.now(),
        mouseMovements: [],
        clicks: [],
        scrollPositions: [],
        keypresses: [],
        focusChanges: [],
        urlChanges: [],
        consoleLogs: [],
        networkRequests: [],
        performanceMetrics: {},
        userActions: [],
        domElements: [],
        formData: {},
        storageChanges: {
            localStorage: {},
            sessionStorage: {},
            cookies: {}
        }
    };

    // Performance monitoring
    let performanceTracker = {
        metrics: {},
        observers: [],
        startTime: performance.now()
    };

    // Network monitoring
    let networkMonitor = {
        requests: [],
        responses: [],
        errors: []
    };

    // Storage monitoring
    let storageMonitor = {
        localStorage: new Map(),
        sessionStorage: new Map(),
        cookies: new Map()
    };

    // Utility functions
    function generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    function generateErrorId() {
        return 'error_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    function log(message, data = null) {
        if (config.debug) {
            console.log('[Metric Points Error Tracker]', message, data);
        }
    }

    function shouldTrack() {
        return Math.random() <= config.sampleRate;
    }

    function sanitizeData(data) {
        if (!config.privacy.maskUserInput) return data;
        
        const sensitive = config.privacy.excludeSensitiveFields;
        const sanitized = JSON.parse(JSON.stringify(data));
        
        function sanitizeObject(obj) {
            for (let key in obj) {
                if (sensitive.some(pattern => key.toLowerCase().includes(pattern))) {
                    obj[key] = '[REDACTED]';
                } else if (typeof obj[key] === 'object' && obj[key] !== null) {
                    sanitizeObject(obj[key]);
                }
            }
        }
        
        sanitizeObject(sanitized);
        return sanitized;
    }

    // Session replay tracking
    class SessionReplayTracker {
        constructor() {
            this.isTracking = false;
            this.eventCount = 0;
        }

        start() {
            if (this.isTracking) return;
            
            log('Starting session replay tracking');
            this.isTracking = true;
            
            this.trackMouseMovements();
            this.trackClicks();
            this.trackScroll();
            this.trackKeypresses();
            this.trackFocus();
            this.trackUrlChanges();
            this.trackConsoleLogs();
            this.trackNetworkRequests();
            this.trackStorageChanges();
            this.trackFormSubmissions();
            this.trackDomChanges();
            
            log('Session replay tracking started');
        }

        stop() {
            this.isTracking = false;
            log('Session replay tracking stopped');
        }

        trackMouseMovements() {
            if (!config.sessionReplay.mouseTracking) return;
            
            let lastMove = 0;
            const throttle = 50; // ms
            
            document.addEventListener('mousemove', (e) => {
                if (!this.isTracking) return;
                
                const now = Date.now();
                if (now - lastMove < throttle) return;
                lastMove = now;
                
                if (this.eventCount >= config.sessionReplay.maxEvents) return;
                
                sessionData.mouseMovements.push({
                    x: e.clientX,
                    y: e.clientY,
                    timestamp: now - sessionData.startTime,
                    element: e.target.tagName + (e.target.id ? '#' + e.target.id : '') + (e.target.className ? '.' + e.target.className.split(' ')[0] : '')
                });
                
                this.eventCount++;
            });
        }

        trackClicks() {
            if (!config.sessionReplay.clickTracking) return;
            
            document.addEventListener('click', (e) => {
                if (!this.isTracking) return;
                
                if (this.eventCount >= config.sessionReplay.maxEvents) return;
                
                const clickData = {
                    element: e.target.tagName + (e.target.id ? '#' + e.target.id : '') + (e.target.className ? '.' + e.target.className.split(' ')[0] : ''),
                    coordinates: { x: e.clientX, y: e.clientY },
                    timestamp: Date.now() - sessionData.startTime,
                    button: e.button,
                    ctrlKey: e.ctrlKey,
                    shiftKey: e.shiftKey,
                    altKey: e.altKey
                };
                
                sessionData.clicks.push(clickData);
                this.eventCount++;
                
                log('Click tracked', clickData);
            });
        }

        trackScroll() {
            if (!config.sessionReplay.scrollTracking) return;
            
            let lastScroll = 0;
            const throttle = 100; // ms
            
            window.addEventListener('scroll', (e) => {
                if (!this.isTracking) return;
                
                const now = Date.now();
                if (now - lastScroll < throttle) return;
                lastScroll = now;
                
                if (this.eventCount >= config.sessionReplay.maxEvents) return;
                
                sessionData.scrollPositions.push({
                    position: { x: window.pageXOffset, y: window.pageYOffset },
                    timestamp: now - sessionData.startTime,
                    element: e.target.tagName || 'window'
                });
                
                this.eventCount++;
            });
        }

        trackKeypresses() {
            if (!config.sessionReplay.keypressTracking) return;
            
            document.addEventListener('keydown', (e) => {
                if (!this.isTracking) return;
                
                if (this.eventCount >= config.sessionReplay.maxEvents) return;
                
                // Skip function keys and modifier keys
                if (e.key.length > 1 && !['Enter', 'Tab', 'Escape', 'Backspace', 'Delete'].includes(e.key)) return;
                
                const keypressData = {
                    key: e.key,
                    code: e.code,
                    timestamp: Date.now() - sessionData.startTime,
                    element: e.target.tagName + (e.target.id ? '#' + e.target.id : '') + (e.target.className ? '.' + e.target.className.split(' ')[0] : ''),
                    ctrlKey: e.ctrlKey,
                    shiftKey: e.shiftKey,
                    altKey: e.altKey
                };
                
                sessionData.keypresses.push(keypressData);
                this.eventCount++;
            });
        }

        trackFocus() {
            if (!config.sessionReplay.focusTracking) return;
            
            document.addEventListener('focusin', (e) => {
                if (!this.isTracking) return;
                
                if (this.eventCount >= config.sessionReplay.maxEvents) return;
                
                sessionData.focusChanges.push({
                    type: 'focus',
                    element: e.target.tagName + (e.target.id ? '#' + e.target.id : '') + (e.target.className ? '.' + e.target.className.split(' ')[0] : ''),
                    timestamp: Date.now() - sessionData.startTime
                });
                
                this.eventCount++;
            });
            
            document.addEventListener('focusout', (e) => {
                if (!this.isTracking) return;
                
                if (this.eventCount >= config.sessionReplay.maxEvents) return;
                
                sessionData.focusChanges.push({
                    type: 'blur',
                    element: e.target.tagName + (e.target.id ? '#' + e.target.id : '') + (e.target.className ? '.' + e.target.className.split(' ')[0] : ''),
                    timestamp: Date.now() - sessionData.startTime
                });
                
                this.eventCount++;
            });
        }

        trackUrlChanges() {
            if (!config.sessionReplay.urlTracking) return;
            
            let currentUrl = window.location.href;
            
            const checkUrl = () => {
                if (window.location.href !== currentUrl) {
                    sessionData.urlChanges.push({
                        from: currentUrl,
                        to: window.location.href,
                        timestamp: Date.now() - sessionData.startTime
                    });
                    currentUrl = window.location.href;
                }
            };
            
            // Check for URL changes periodically
            setInterval(checkUrl, 1000);
            
            // Listen for pushstate/replacestate
            const originalPushState = history.pushState;
            const originalReplaceState = history.replaceState;
            
            history.pushState = function(...args) {
                originalPushState.apply(this, args);
                checkUrl();
            };
            
            history.replaceState = function(...args) {
                originalReplaceState.apply(this, args);
                checkUrl();
            };
        }

        trackConsoleLogs() {
            if (!this.isTracking) return;
            
            const originalConsole = {
                log: console.log,
                warn: console.warn,
                error: console.error,
                info: console.info,
                debug: console.debug
            };
            
            ['log', 'warn', 'error', 'info', 'debug'].forEach(level => {
                console[level] = (...args) => {
                    originalConsole[level].apply(console, args);
                    
                    if (this.eventCount >= config.sessionReplay.maxEvents) return;
                    
                    sessionData.consoleLogs.push({
                        level: level,
                        message: args.map(arg => typeof arg === 'object' ? JSON.stringify(arg) : String(arg)).join(' '),
                        timestamp: Date.now() - sessionData.startTime,
                        stack: new Error().stack
                    });
                    
                    this.eventCount++;
                };
            });
        }

        trackNetworkRequests() {
            if (!this.isTracking) return;
            
            // Intercept XMLHttpRequest
            const originalXHROpen = XMLHttpRequest.prototype.open;
            const originalXHRSend = XMLHttpRequest.prototype.send;
            
            XMLHttpRequest.prototype.open = function(method, url, ...args) {
                this._metricPointsMethod = method;
                this._metricPointsUrl = url;
                return originalXHROpen.apply(this, [method, url, ...args]);
            };
            
            XMLHttpRequest.prototype.send = function(data) {
                const startTime = Date.now();
                const xhr = this;
                
                xhr.addEventListener('load', () => {
                    if (this.eventCount >= config.sessionReplay.maxEvents) return;
                    
                    sessionData.networkRequests.push({
                        method: xhr._metricPointsMethod,
                        url: xhr._metricPointsUrl,
                        status: xhr.status,
                        duration: Date.now() - startTime,
                        timestamp: startTime - sessionData.startTime,
                        responseSize: xhr.responseText?.length || 0
                    });
                    
                    this.eventCount++;
                });
                
                xhr.addEventListener('error', () => {
                    if (this.eventCount >= config.sessionReplay.maxEvents) return;
                    
                    sessionData.networkRequests.push({
                        method: xhr._metricPointsMethod,
                        url: xhr._metricPointsUrl,
                        status: 'error',
                        duration: Date.now() - startTime,
                        timestamp: startTime - sessionData.startTime,
                        error: 'Network error'
                    });
                    
                    this.eventCount++;
                });
                
                return originalXHRSend.apply(this, [data]);
            };
            
            // Intercept fetch
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                const startTime = Date.now();
                const method = options.method || 'GET';
                
                return originalFetch(url, options).then(response => {
                    if (this.eventCount >= config.sessionReplay.maxEvents) return response;
                    
                    sessionData.networkRequests.push({
                        method: method,
                        url: typeof url === 'string' ? url : url.toString(),
                        status: response.status,
                        duration: Date.now() - startTime,
                        timestamp: startTime - sessionData.startTime,
                        responseSize: 0 // Would need to clone response to get size
                    });
                    
                    this.eventCount++;
                    return response;
                }).catch(error => {
                    if (this.eventCount >= config.sessionReplay.maxEvents) return Promise.reject(error);
                    
                    sessionData.networkRequests.push({
                        method: method,
                        url: typeof url === 'string' ? url : url.toString(),
                        status: 'error',
                        duration: Date.now() - startTime,
                        timestamp: startTime - sessionData.startTime,
                        error: error.message
                    });
                    
                    this.eventCount++;
                    return Promise.reject(error);
                });
            };
        }

        trackStorageChanges() {
            if (!this.isTracking) return;
            
            // Monitor localStorage
            const originalSetItem = Storage.prototype.setItem;
            Storage.prototype.setItem = function(key, value) {
                originalSetItem.apply(this, [key, value]);
                
                if (this === localStorage) {
                    sessionData.storageChanges.localStorage[key] = value;
                } else if (this === sessionStorage) {
                    sessionData.storageChanges.sessionStorage[key] = value;
                }
            };
            
            // Monitor cookies
            const originalDocumentCookie = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie');
            Object.defineProperty(document, 'cookie', {
                set: function(value) {
                    originalDocumentCookie.set.call(this, value);
                    
                    const [name] = value.split('=');
                    sessionData.storageChanges.cookies[name] = value;
                },
                get: function() {
                    return originalDocumentCookie.get.call(this);
                }
            });
        }

        trackFormSubmissions() {
            if (!this.isTracking) return;
            
            document.addEventListener('submit', (e) => {
                if (this.eventCount >= config.sessionReplay.maxEvents) return;
                
                const formData = new FormData(e.target);
                const formObject = {};
                
                for (let [key, value] of formData.entries()) {
                    if (config.privacy.excludeSensitiveFields.some(pattern => key.toLowerCase().includes(pattern))) {
                        formObject[key] = '[REDACTED]';
                    } else {
                        formObject[key] = value;
                    }
                }
                
                sessionData.formData[e.target.action || 'unknown'] = formObject;
                this.eventCount++;
            });
        }

        trackDomChanges() {
            if (!this.isTracking) return;
            
            // Track DOM mutations
            const observer = new MutationObserver((mutations) => {
                if (this.eventCount >= config.sessionReplay.maxEvents) return;
                
                mutations.forEach(mutation => {
                    if (mutation.type === 'childList') {
                        sessionData.domElements.push({
                            type: 'DOM_CHANGE',
                            action: 'childList',
                            target: mutation.target.tagName + (mutation.target.id ? '#' + mutation.target.id : ''),
                            timestamp: Date.now() - sessionData.startTime
                        });
                    }
                });
                
                this.eventCount++;
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    // Performance monitoring
    class PerformanceTracker {
        constructor() {
            this.metrics = {};
            this.observers = [];
        }

        start() {
            log('Starting performance tracking');
            this.captureInitialMetrics();
            this.startMemoryMonitoring();
            this.startPerformanceObserver();
        }

        captureInitialMetrics() {
            if (window.performance && window.performance.timing) {
                const timing = window.performance.timing;
                const navigationStart = timing.navigationStart;
                
                this.metrics = {
                    pageLoadTime: timing.loadEventEnd - navigationStart,
                    timeToFirstByte: timing.responseStart - navigationStart,
                    domContentLoaded: timing.domContentLoadedEventEnd - navigationStart,
                    timeToInteractive: timing.domInteractive - navigationStart,
                    dnsLookup: timing.domainLookupEnd - timing.domainLookupStart,
                    tcpConnection: timing.connectEnd - timing.connectStart,
                    serverResponse: timing.responseEnd - timing.responseStart,
                    domProcessing: timing.domComplete - timing.domLoading
                };
            }
            
            // Modern Performance API
            if (window.performance && window.performance.getEntriesByType) {
                const navigationEntries = window.performance.getEntriesByType('navigation');
                if (navigationEntries.length > 0) {
                    const nav = navigationEntries[0];
                    this.metrics = {
                        ...this.metrics,
                        pageLoadTime: nav.loadEventEnd - nav.startTime,
                        timeToFirstByte: nav.responseStart - nav.startTime,
                        domContentLoaded: nav.domContentLoadedEventEnd - nav.startTime,
                        timeToInteractive: nav.domInteractive - nav.startTime
                    };
                }
            }
            
            sessionData.performanceMetrics = this.metrics;
        }

        startMemoryMonitoring() {
            if (window.performance && window.performance.memory) {
                setInterval(() => {
                    const memory = window.performance.memory;
                    this.metrics.memoryUsage = Math.round(memory.usedJSHeapSize / 1024 / 1024);
                    this.metrics.memoryLimit = Math.round(memory.jsHeapSizeLimit / 1024 / 1024);
                    this.metrics.memoryTotal = Math.round(memory.totalJSHeapSize / 1024 / 1024);
                    
                    sessionData.performanceMetrics = this.metrics;
                }, 5000);
            }
        }

        startPerformanceObserver() {
            if (window.PerformanceObserver) {
                try {
                    const observer = new PerformanceObserver((list) => {
                        list.getEntries().forEach(entry => {
                            if (entry.entryType === 'measure') {
                                this.metrics[entry.name] = entry.duration;
                            }
                        });
                    });
                    
                    observer.observe({ entryTypes: ['measure'] });
                    this.observers.push(observer);
                } catch (e) {
                    log('PerformanceObserver not supported', e);
                }
            }
        }

        stop() {
            this.observers.forEach(observer => observer.disconnect());
            log('Performance tracking stopped');
        }
    }

    // Error tracking
    class ErrorTracker {
        constructor() {
            this.errors = [];
            this.isInitialized = false;
        }

        start() {
            if (this.isInitialized) return;
            
            log('Starting error tracking');
            
            this.setupErrorHandlers();
            this.setupPromiseRejectionHandler();
            this.setupUnhandledRejectionHandler();
            
            this.isInitialized = true;
            log('Error tracking started');
        }

        setupErrorHandlers() {
            window.addEventListener('error', (event) => {
                this.captureError({
                    type: 'javascript',
                    message: event.message,
                    filename: event.filename,
                    lineno: event.lineno,
                    colno: event.colno,
                    error: event.error,
                    stack: event.error ? event.error.stack : null,
                    timestamp: new Date().toISOString(),
                    url: window.location.href,
                    userAgent: navigator.userAgent
                });
            });
        }

        setupPromiseRejectionHandler() {
            window.addEventListener('unhandledrejection', (event) => {
                this.captureError({
                    type: 'promise',
                    message: event.reason?.message || 'Unhandled Promise Rejection',
                    stack: event.reason?.stack || null,
                    timestamp: new Date().toISOString(),
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    reason: event.reason
                });
            });
        }

        setupUnhandledRejectionHandler() {
            // For older browsers
            window.onunhandledrejection = (event) => {
                this.captureError({
                    type: 'promise',
                    message: event.reason?.message || 'Unhandled Promise Rejection',
                    stack: event.reason?.stack || null,
                    timestamp: new Date().toISOString(),
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    reason: event.reason
                });
            };
        }

        captureError(errorData) {
            if (!shouldTrack()) return;
            
            log('Error captured', errorData);
            
            const enhancedError = this.enhanceErrorData(errorData);
            this.errors.push(enhancedError);
            
            // Send error immediately
            this.sendError(enhancedError);
        }

        enhanceErrorData(errorData) {
            const enhanced = {
                ...errorData,
                id: generateErrorId(),
                sessionId: sessionData.sessionId,
                timestamp: new Date().toISOString(),
                url: window.location.href,
                referrer: document.referrer,
                userAgent: navigator.userAgent,
                
                // Browser and device info
                browser: this.getBrowserInfo(),
                device: this.getDeviceInfo(),
                screen: this.getScreenInfo(),
                viewport: this.getViewportInfo(),
                language: navigator.language,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                
                // Performance context
                performance: sessionData.performanceMetrics,
                
                // Session replay data (limited for privacy)
                sessionReplay: {
                    mouseMovements: sessionData.mouseMovements.slice(-50), // Last 50 movements
                    clicks: sessionData.clicks.slice(-20), // Last 20 clicks
                    scrollPositions: sessionData.scrollPositions.slice(-10), // Last 10 scrolls
                    keypresses: sessionData.keypresses.slice(-30), // Last 30 keypresses
                    focusChanges: sessionData.focusChanges.slice(-20), // Last 20 focus changes
                    urlChanges: sessionData.urlChanges.slice(-5) // Last 5 URL changes
                },
                
                // Page context
                pageContext: {
                    title: document.title,
                    url: window.location.href,
                    referrer: document.referrer,
                    hash: window.location.hash
                },
                
                // User actions
                userActions: sessionData.userActions.slice(-20), // Last 20 actions
                
                // Form data
                formData: sessionData.formData,
                
                // DOM elements
                domElements: sessionData.domElements.slice(-20), // Last 20 DOM changes
                
                // Network requests
                networkRequests: sessionData.networkRequests.slice(-20), // Last 20 requests
                
                // Console logs
                consoleLogs: sessionData.consoleLogs.slice(-20), // Last 20 logs
                
                // Storage changes
                storageChanges: sessionData.storageChanges,
                
                // Query parameters
                queryParameters: this.getQueryParameters(),
                
                // Headers (basic)
                headers: {
                    'User-Agent': navigator.userAgent,
                    'Accept-Language': navigator.language,
                    'Referer': document.referrer
                }
            };
            
            return enhanced;
        }

        getBrowserInfo() {
            const userAgent = navigator.userAgent;
            let browser = 'Unknown';
            let version = 'Unknown';
            
            if (userAgent.includes('Chrome')) {
                browser = 'Chrome';
                const match = userAgent.match(/Chrome\/(\d+\.\d+)/);
                if (match) version = match[1];
            } else if (userAgent.includes('Firefox')) {
                browser = 'Firefox';
                const match = userAgent.match(/Firefox\/(\d+\.\d+)/);
                if (match) version = match[1];
            } else if (userAgent.includes('Safari')) {
                browser = 'Safari';
                const match = userAgent.match(/Version\/(\d+\.\d+)/);
                if (match) version = match[1];
            } else if (userAgent.includes('Edge')) {
                browser = 'Edge';
                const match = userAgent.match(/Edge\/(\d+\.\d+)/);
                if (match) version = match[1];
            }
            
            return { name: browser, version: version };
        }

        getDeviceInfo() {
            const userAgent = navigator.userAgent;
            let deviceType = 'desktop';
            
            if (userAgent.includes('Mobile')) deviceType = 'mobile';
            else if (userAgent.includes('Tablet')) deviceType = 'tablet';
            else if (userAgent.includes('Bot') || userAgent.includes('Crawler')) deviceType = 'robot';
            
            return { type: deviceType };
        }

        getScreenInfo() {
            return {
                width: screen.width,
                height: screen.height,
                colorDepth: screen.colorDepth,
                pixelDepth: screen.pixelDepth
            };
        }

        getViewportInfo() {
            return {
                width: window.innerWidth,
                height: window.innerHeight
            };
        }

        getQueryParameters() {
            const params = new URLSearchParams(window.location.search);
            const result = {};
            
            for (let [key, value] of params.entries()) {
                if (config.privacy.excludeSensitiveFields.some(pattern => key.toLowerCase().includes(pattern))) {
                    result[key] = '[REDACTED]';
                } else {
                    result[key] = value;
                }
            }
            
            return result;
        }

        sendError(errorData) {
            if (!config.apiKey || !config.endpoint) {
                log('Cannot send error: missing API key or endpoint');
                return;
            }
            
            const payload = {
                ...errorData,
                metadata: config.metadata
            };
            
            // Sanitize sensitive data
            const sanitizedPayload = sanitizeData(payload);
            
            log('Sending error data', sanitizedPayload);
            
            // Use XMLHttpRequest for better error handling
            const xhr = new XMLHttpRequest();
            xhr.open('POST', config.endpoint, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            
            xhr.onload = function() {
                if (xhr.status === 200 || xhr.status === 204) {
                    log('Error data sent successfully');
                } else {
                    log('Failed to send error data', { status: xhr.status, response: xhr.responseText });
                }
            };
            
            xhr.onerror = function() {
                log('Network error while sending error data');
            };
            
            xhr.send(JSON.stringify(sanitizedPayload));
        }
    }

    // Main tracker class
    class MetricPointsErrorTracker {
        constructor(userConfig) {
            this.config = { ...config, ...userConfig };
            this.sessionReplay = new SessionReplayTracker();
            this.performance = new PerformanceTracker();
            this.errorTracker = new ErrorTracker();
            this.isStarted = false;
        }

        start() {
            if (this.isStarted) {
                log('Tracker already started');
                return;
            }
            
            log('Starting Metric Points Error Tracker', this.config);
            
            // Validate configuration
            if (!this.config.apiKey) {
                log('Error: API key is required');
                return;
            }
            
            if (!this.config.endpoint) {
                log('Error: Endpoint is required');
                return;
            }
            
            // Start all tracking systems
            this.sessionReplay.start();
            this.performance.start();
            this.errorTracker.start();
            
            this.isStarted = true;
            log('Metric Points Error Tracker started successfully');
        }

        stop() {
            if (!this.isStarted) return;
            
            log('Stopping Metric Points Error Tracker');
            
            this.sessionReplay.stop();
            this.performance.stop();
            this.isStarted = false;
            
            log('Metric Points Error Tracker stopped');
        }

        // Public API methods
        trackError(error, context = {}) {
            if (!this.isStarted) {
                log('Tracker not started, cannot track error');
                return;
            }
            
            const errorData = {
                type: 'manual',
                message: error.message || String(error),
                stack: error.stack,
                timestamp: new Date().toISOString(),
                url: window.location.href,
                userAgent: navigator.userAgent,
                ...context
            };
            
            this.errorTracker.captureError(errorData);
        }

        trackEvent(eventName, data = {}) {
            if (!this.isStarted) return;
            
            sessionData.userActions.push({
                event: eventName,
                data: data,
                timestamp: Date.now() - sessionData.startTime
            });
            
            log('Event tracked', { event: eventName, data: data });
        }

        setMetadata(key, value) {
            this.config.metadata[key] = value;
            log('Metadata updated', { key, value });
        }

        getSessionData() {
            return { ...sessionData };
        }

        getPerformanceMetrics() {
            return { ...this.performance.metrics };
        }
    }

    // Initialize tracker when configuration is available
    let tracker = null;
    
    function initializeTracker() {
        if (window.MetricPointsConfig && !tracker) {
            tracker = new MetricPointsErrorTracker(window.MetricPointsConfig);
            tracker.start();
            
            // Expose tracker globally for manual usage
            window.MetricPointsTracker = tracker;
            
            log('Tracker initialized with configuration', window.MetricPointsConfig);
        }
    }
    
    // Try to initialize immediately if config is already available
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeTracker);
    } else {
        initializeTracker();
    }
    
    // Also try on window load for late configuration
    window.addEventListener('load', initializeTracker);
    
    // Fallback initialization
    setTimeout(initializeTracker, 1000);
    
    // Expose global error tracking function
    window.trackError = function(error, context) {
        if (tracker) {
            tracker.trackError(error, context);
        } else {
            log('Tracker not initialized, queuing error');
            // Queue error for later
            if (!window._queuedErrors) window._queuedErrors = [];
            window._queuedErrors.push({ error, context });
        }
    };
    
    // Process queued errors when tracker is ready
    if (window._queuedErrors) {
        window._queuedErrors.forEach(({ error, context }) => {
            if (tracker) {
                tracker.trackError(error, context);
            }
        });
        delete window._queuedErrors;
    }
    
    log('Metric Points Error Tracker loaded');
})(); 