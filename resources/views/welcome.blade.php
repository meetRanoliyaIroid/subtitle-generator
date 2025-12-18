<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Subtitle Generator - Create Professional Subtitles for Your Videos</title>
    <meta name="description" content="Upload your video and get automatic subtitle generation with embedded subtitles. Perfect for content creators, educators, and businesses.">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--gray-800);
        }

        .hero-section {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .upload-zone {
            border: 3px dashed #cbd5e1;
            border-radius: 12px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .upload-zone:hover {
            border-color: var(--primary-color);
            background: rgba(255,255,255,0.95);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .upload-zone.dragover {
            border-color: var(--success-color);
            background: rgba(16, 185, 129, 0.1);
        }

        .upload-zone.uploading {
            border-color: var(--warning-color);
            background: rgba(245, 158, 11, 0.1);
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
        }

        .feature-card {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .status-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #059669);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-uploaded { background-color: #dbeafe; color: #1e40af; }
        .status-processing_subtitle { background-color: #fef3c7; color: #d97706; }
        .status-subtitle_generated { background-color: #d1fae5; color: #065f46; }
        .status-processing_video { background-color: #fef3c7; color: #d97706; }
        .status-completed { background-color: #d1fae5; color: #065f46; }
        .status-failed { background-color: #fee2e2; color: #dc2626; }

        .video-preview {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .glass-effect {
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.18);
        }

        @media (max-width: 768px) {
            .hero-section {
                margin: 20px;
                padding: 30px 20px;
            }

            .upload-zone {
                padding: 40px 20px;
            }

            .feature-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-effect position-fixed top-0 w-100 z-index-1030" style="z-index: 1030;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-film me-2"></i>
                Subtitle Generator
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('videos.index') }}">My Videos</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-0">
        <!-- Hero Section -->
        <section class="hero-section mx-auto mt-5 mb-5" style="max-width: 1200px;">
            <div class="text-center py-5 px-4">
                <h1 class="display-4 fw-bold text-white mb-4">
                    Generate Professional Subtitles<br>
                    <span class="text-warning">in Minutes</span>
                </h1>
                <p class="lead text-white-50 mb-5 fs-5">
                    Upload your video and get automatic subtitle generation with embedded subtitles.
                    Perfect for content creators, educators, and businesses.
                </p>

                <!-- Upload Zone -->
                <div class="upload-zone p-5 mb-4 mx-auto" style="max-width: 600px;" id="uploadZone">
                    <div class="text-center">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <h5 class="mb-3">Drag & Drop Your Video Here</h5>
                        <p class="text-muted mb-3">or click to browse files</p>
                        <input type="file" id="videoInput" accept="video/*" class="d-none">
                        <button class="btn btn-primary mb-3" onclick="document.getElementById('videoInput').click()">
                            <i class="fas fa-upload me-2"></i>Choose Video File
                        </button>
                        <p class="small text-muted">Supported formats: MP4, AVI, MOV, WMV, FLV, WEBM (Max: 100MB)</p>
                    </div>
                </div>

                <!-- Upload Progress (Hidden initially) -->
                <div class="status-card p-4 mx-auto d-none" style="max-width: 600px;" id="uploadProgress">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-spinner fa-spin me-3 text-primary"></i>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Uploading Video...</h6>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" id="uploadProgressBar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mb-0" id="uploadStatus">Preparing upload...</p>
                </div>

                <!-- Processing Status (Hidden initially) -->
                <div class="status-card p-4 mx-auto d-none" style="max-width: 600px;" id="processingStatus">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-cog fa-spin fa-2x text-primary mb-3"></i>
                            <h6>Processing Your Video</h6>
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="status-badge status-processing_subtitle mb-2">Generating Subtitles</div>
                                <small class="text-muted">Step 1 of 2</small>
                            </div>
                            <div class="col-6">
                                <div class="status-badge status-uploaded mb-2">Embedding Subtitles</div>
                                <small class="text-muted">Step 2 of 2</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Section (Hidden initially) -->
                <div class="status-card p-4 mx-auto d-none" style="max-width: 600px;" id="resultsSection">
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="mb-4">Processing Complete!</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="#" class="btn btn-success w-100" id="downloadSubtitles">
                                    <i class="fas fa-download me-2"></i>
                                    Download Subtitles (.srt)
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="#" class="btn btn-success w-100" id="downloadVideo">
                                    <i class="fas fa-download me-2"></i>
                                    Download Video with Subtitles
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-top">
                            <button class="btn btn-outline-primary" onclick="resetUpload()">
                                <i class="fas fa-plus me-2"></i>Process Another Video
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error Section (Hidden initially) -->
                <div class="alert alert-danger mx-auto d-none" style="max-width: 600px;" id="errorSection">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="errorMessage">An error occurred while processing your video.</span>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold text-white mb-4">Why Choose Our Subtitle Generator?</h2>
                    <p class="lead text-white-50">Professional subtitle generation made simple and accessible</p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card p-4 h-100">
                            <div class="text-center mb-3">
                                <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">AI-Powered</h5>
                            </div>
                            <p class="text-muted">Advanced AI technology automatically transcribes your videos with high accuracy, understanding context and speaker changes.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card p-4 h-100">
                            <div class="text-center mb-3">
                                <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Mobile Friendly</h5>
                            </div>
                            <p class="text-muted">Upload videos directly from your mobile device. Our responsive design works perfectly on all screen sizes.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card p-4 h-100">
                            <div class="text-center mb-3">
                                <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Fast Processing</h5>
                            </div>
                            <p class="text-muted">Get your subtitles in minutes, not hours. Our optimized processing pipeline handles videos of various lengths efficiently.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card p-4 h-100">
                            <div class="text-center mb-3">
                                <i class="fas fa-file-video fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Multiple Formats</h5>
                            </div>
                            <p class="text-muted">Download subtitles as .srt files or get your video with embedded subtitles in MP4 format, ready for any platform.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card p-4 h-100">
                            <div class="text-center mb-3">
                                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Secure & Private</h5>
                            </div>
                            <p class="text-muted">Your videos are processed securely and automatically deleted after processing. We prioritize your privacy and data security.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card p-4 h-100">
                            <div class="text-center mb-3">
                                <i class="fas fa-globe fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Multi-Language</h5>
                            </div>
                            <p class="text-muted">Support for multiple languages and automatic language detection. Perfect for global content creators.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="py-5 bg-white">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-4">How It Works</h2>
                    <p class="lead text-muted">Three simple steps to professional subtitles</p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4 text-center">
                        <div class="mb-4">
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <span class="text-white fw-bold fs-3">1</span>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-3">Upload Your Video</h5>
                        <p class="text-muted">Drag and drop your video file or click to browse. We support all major video formats up to 100MB.</p>
                    </div>

                    <div class="col-lg-4 text-center">
                        <div class="mb-4">
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <span class="text-white fw-bold fs-3">2</span>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-3">AI Processing</h5>
                        <p class="text-muted">Our AI analyzes your video, transcribes the audio, and generates accurate timestamps for each subtitle.</p>
                    </div>

                    <div class="col-lg-4 text-center">
                        <div class="mb-4">
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <span class="text-white fw-bold fs-3">3</span>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-3">Download Results</h5>
                        <p class="text-muted">Get both the subtitle file (.srt) and your video with embedded subtitles, ready to use anywhere.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-dark text-white py-4">
            <div class="container text-center">
                <p class="mb-0">&copy; 2024 Subtitle Generator. Built with Laravel & AI technology.</p>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script>
        let currentVideoId = null;
        let statusCheckInterval = null;

        // Upload zone interactions
        const uploadZone = document.getElementById('uploadZone');
        const videoInput = document.getElementById('videoInput');
        const uploadProgress = document.getElementById('uploadProgress');
        const processingStatus = document.getElementById('processingStatus');
        const resultsSection = document.getElementById('resultsSection');
        const errorSection = document.getElementById('errorSection');

        // Drag and drop functionality
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        uploadZone.addEventListener('click', () => {
            videoInput.click();
        });

        videoInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            // Validate file type
            if (!file.type.startsWith('video/')) {
                showError('Please select a valid video file.');
                return;
            }

            // Validate file size (100MB)
            if (file.size > 100 * 1024 * 1024) {
                showError('File size must be less than 100MB.');
                return;
            }

            uploadVideo(file);
        }

        function uploadVideo(file) {
            const formData = new FormData();
            formData.append('video', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Show upload progress
            uploadZone.classList.add('d-none');
            uploadProgress.classList.remove('d-none');

            // Simulate upload progress (in real implementation, this would track actual upload)
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                document.getElementById('uploadProgressBar').style.width = progress + '%';

                if (progress >= 90) {
                    clearInterval(progressInterval);
                    document.getElementById('uploadStatus').textContent = 'Processing upload...';
                }
            }, 200);

            fetch('{{ route("videos.store") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                document.getElementById('uploadProgressBar').style.width = '100%';

                if (data.success) {
                    currentVideoId = data.video_id;
                    uploadProgress.classList.add('d-none');
                    processingStatus.classList.remove('d-none');
                    startStatusChecking();
                } else {
                    showError(data.message || 'Upload failed. Please try again.');
                }
            })
            .catch(error => {
                clearInterval(progressInterval);
                showError('Network error. Please check your connection and try again.');
            });
        }

        function startStatusChecking() {
            statusCheckInterval = setInterval(() => {
                fetch(`/videos/${currentVideoId}/status`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'completed') {
                            clearInterval(statusCheckInterval);
                            processingStatus.classList.add('d-none');
                            resultsSection.classList.remove('d-none');

                            // Update download links
                            document.getElementById('downloadSubtitles').href = data.subtitle_url;
                            document.getElementById('downloadVideo').href = data.video_url;
                        } else if (data.status === 'failed') {
                            clearInterval(statusCheckInterval);
                            processingStatus.classList.add('d-none');
                            showError(data.error_message || 'Processing failed. Please try again.');
                        }
                        // Continue checking for other statuses
                    })
                    .catch(error => {
                        console.error('Status check failed:', error);
                    });
            }, 3000); // Check every 3 seconds
        }

        function showError(message) {
            uploadZone.classList.remove('d-none');
            uploadProgress.classList.add('d-none');
            processingStatus.classList.add('d-none');
            resultsSection.classList.add('d-none');

            document.getElementById('errorMessage').textContent = message;
            errorSection.classList.remove('d-none');

            // Auto-hide error after 5 seconds
            setTimeout(() => {
                errorSection.classList.add('d-none');
            }, 5000);
        }

        function resetUpload() {
            // Reset all states
            uploadZone.classList.remove('d-none');
            uploadProgress.classList.add('d-none');
            processingStatus.classList.add('d-none');
            resultsSection.classList.add('d-none');
            errorSection.classList.add('d-none');

            // Clear file input
            videoInput.value = '';

            // Clear progress bar
            document.getElementById('uploadProgressBar').style.width = '0%';

            // Clear any intervals
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }

            currentVideoId = null;
        }

        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add fade-in animation to sections
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .container > .row').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>