<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ollama Facial Verification</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Tailwind CDN (Prevents build crashes on Railway) -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #0f172a, #000000);
            background-attachment: fixed;
            min-height: 100vh;
        }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        }

        .drop-zone.active {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.1);
        }

        /* Subtle animated gradient for the button */
        .btn-gradient {
            background-size: 200% 200%;
            background-image: linear-gradient(to right, #4f46e5, #ec4899, #4f46e5);
            animation: gradient-shift 3s ease infinite;
        }

        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Floating animation for decorative elements */
        .float {
            animation: floating 6s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="text-white antialiased overflow-x-hidden selection:bg-indigo-500 selection:text-white">

    <!-- Decorative background blobs -->
    <div class="fixed top-[-10%] left-[-10%] w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 float"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-96 h-96 bg-fuchsia-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 float" style="animation-delay: -3s;"></div>

    <div class="relative min-h-screen flex flex-col items-center justify-center p-4 sm:p-8">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-5xl sm:text-7xl font-extrabold tracking-tight mb-4 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-fuchsia-400">
                Ollama Vision
            </h1>
            <p class="text-lg sm:text-xl text-gray-400 font-medium max-w-2xl mx-auto">
                Next-generation facial verification powered by local AI.
            </p>
        </div>

        <!-- Main Content Grid -->
        <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            
            <!-- Upload Panel -->
            <div class="glass-panel rounded-3xl p-8 flex flex-col relative overflow-hidden transition-all duration-500 hover:shadow-2xl hover:shadow-indigo-500/10">
                <h2 class="text-2xl font-bold mb-2 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Upload Face
                </h2>
                <p class="text-gray-400 mb-6 text-sm">Drag and drop a high-quality image of a face to begin verification.</p>

                <!-- Dropzone -->
                <div id="dropzone" class="drop-zone border-2 border-dashed border-gray-600 rounded-2xl h-80 flex flex-col items-center justify-center cursor-pointer transition-all duration-300 relative group overflow-hidden">
                    
                    <input type="file" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/jpeg, image/png, image/jpg" />
                    
                    <!-- Placeholder State -->
                    <div id="uploadPlaceholder" class="flex flex-col items-center justify-center text-center p-6 z-0 transition-opacity duration-300">
                        <div class="w-16 h-16 mb-4 rounded-full bg-gray-800 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </div>
                        <p class="text-gray-300 font-medium">Click or drag image here</p>
                        <p class="text-gray-500 text-sm mt-1">JPEG, PNG up to 5MB</p>
                    </div>

                    <!-- Preview State (Hidden initially) -->
                    <div id="imagePreviewContainer" class="absolute inset-0 w-full h-full bg-black/50 backdrop-blur-sm flex items-center justify-center hidden z-0">
                        <img id="imagePreview" src="" alt="Preview" class="max-h-full max-w-full object-contain rounded-xl shadow-2xl" />
                    </div>
                </div>

                <!-- Action Button -->
                <button id="verifyBtn" disabled class="mt-6 w-full py-4 rounded-xl font-bold text-lg text-white opacity-50 cursor-not-allowed transition-all duration-300 bg-gray-800">
                    <span id="btnText">Verify Identity</span>
                    <div id="btnSpinner" class="hidden inline-block ml-2 w-5 h-5 border-2 border-white/20 border-t-white rounded-full animate-spin align-middle"></div>
                </button>
                
                <div id="errorMsg" class="mt-4 text-red-400 text-sm text-center hidden"></div>
            </div>

            <!-- Results Panel -->
            <div id="resultsPanel" class="glass-panel rounded-3xl p-8 flex flex-col h-full relative overflow-hidden transition-all duration-700 opacity-50 grayscale">
                <h2 class="text-2xl font-bold mb-2 flex items-center gap-2">
                    <svg class="w-6 h-6 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Analysis Results
                </h2>
                <p class="text-gray-400 mb-6 text-sm">AI-generated features and verification data.</p>
                
                <div class="flex-1 bg-gray-900/50 rounded-2xl p-6 border border-gray-700/50 relative overflow-hidden">
                    
                    <!-- Waiting State -->
                    <div id="waitingState" class="absolute inset-0 flex flex-col items-center justify-center text-gray-500">
                        <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        <p>Awaiting image upload...</p>
                    </div>

                    <!-- Scanning State -->
                    <div id="scanningState" class="absolute inset-0 flex flex-col items-center justify-center hidden">
                        <div class="relative w-24 h-24 mb-4">
                            <div class="absolute inset-0 rounded-full border-4 border-indigo-500/20"></div>
                            <div class="absolute inset-0 rounded-full border-4 border-t-fuchsia-500 animate-spin"></div>
                            <svg class="absolute inset-0 m-auto w-8 h-8 text-indigo-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <p class="text-indigo-300 font-medium animate-pulse">Ollama is analyzing the image...</p>
                    </div>

                    <!-- Result Data -->
                    <div id="resultData" class="hidden h-full flex flex-col">
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-700">
                            <div class="w-3 h-3 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.6)] animate-pulse"></div>
                            <span class="font-bold text-green-400 tracking-wider text-sm uppercase">Verification Complete</span>
                        </div>
                        <div id="resultText" class="text-gray-300 leading-relaxed overflow-y-auto whitespace-pre-wrap pr-2 text-sm">
                            <!-- Results inject here -->
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Script for handling UI and API interaction -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('fileInput');
            const uploadPlaceholder = document.getElementById('uploadPlaceholder');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const imagePreview = document.getElementById('imagePreview');
            const verifyBtn = document.getElementById('verifyBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const errorMsg = document.getElementById('errorMsg');
            
            const resultsPanel = document.getElementById('resultsPanel');
            const waitingState = document.getElementById('waitingState');
            const scanningState = document.getElementById('scanningState');
            const resultData = document.getElementById('resultData');
            const resultText = document.getElementById('resultText');

            let selectedFile = null;

            // Handle Drag & Drop styling
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => dropzone.classList.add('active'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => dropzone.classList.remove('active'), false);
            });

            // Handle file selection
            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt.files && dt.files.length > 0) {
                    handleFile(dt.files[0]);
                }
            });

            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    handleFile(this.files[0]);
                }
            });

            function handleFile(file) {
                errorMsg.classList.add('hidden');
                
                // Validate file type
                if (!file.type.match('image/jpeg') && !file.type.match('image/png')) {
                    showError('Please select a valid JPEG or PNG image.');
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showError('Image must be less than 5MB.');
                    return;
                }

                selectedFile = file;

                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.src = e.target.result;
                    uploadPlaceholder.classList.add('opacity-0');
                    setTimeout(() => {
                        uploadPlaceholder.classList.add('hidden');
                        imagePreviewContainer.classList.remove('hidden');
                        
                        // Enable button
                        verifyBtn.disabled = false;
                        verifyBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-gray-800');
                        verifyBtn.classList.add('btn-gradient', 'shadow-lg', 'shadow-indigo-500/50', 'hover:scale-[1.02]');
                    }, 300);
                }
                reader.readAsDataURL(file);
            }

            function showError(msg) {
                errorMsg.textContent = msg;
                errorMsg.classList.remove('hidden');
            }

            // Handle Verification API Call
            verifyBtn.addEventListener('click', async () => {
                if (!selectedFile) return;

                // UI Loading State
                verifyBtn.disabled = true;
                btnText.textContent = 'Analyzing...';
                btnSpinner.classList.remove('hidden');
                errorMsg.classList.add('hidden');

                // Panel Transitions
                resultsPanel.classList.remove('opacity-50', 'grayscale');
                waitingState.classList.add('hidden');
                resultData.classList.add('hidden');
                scanningState.classList.remove('hidden');

                const formData = new FormData();
                formData.append('image', selectedFile);

                try {
                    const response = await fetch('/api/verify', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Verification failed. Please try again.');
                    }

                    // Success State
                    scanningState.classList.add('hidden');
                    resultData.classList.remove('hidden');
                    
                    // Animate text typing effect
                    resultText.innerHTML = '';
                    const text = data.verification_result || 'Verification complete. No text returned.';
                    let i = 0;
                    
                    function typeWriter() {
                        if (i < text.length) {
                            resultText.innerHTML += text.charAt(i);
                            i++;
                            setTimeout(typeWriter, 15);
                        }
                    }
                    typeWriter();

                } catch (err) {
                    showError(err.message);
                    scanningState.classList.add('hidden');
                    waitingState.classList.remove('hidden');
                    resultsPanel.classList.add('opacity-50', 'grayscale');
                } finally {
                    // Reset Button
                    verifyBtn.disabled = false;
                    btnText.textContent = 'Verify Identity';
                    btnSpinner.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
