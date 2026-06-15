    </div>
    <script>
    function toggleMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    // Close menu when clicking on a link
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                toggleMobileMenu();
            }
        });
    });
    
    // Rewind/Forward audio functions
    function rewindAudio(event) {
        event.preventDefault();
        event.stopPropagation();
        const audio = event.target.closest('.audio-controls').querySelector('audio');
        audio.currentTime = Math.max(0, audio.currentTime - 5);
    }
    
    function forwardAudio(event) {
        event.preventDefault();
        event.stopPropagation();
        const audio = event.target.closest('.audio-controls').querySelector('audio');
        audio.currentTime = Math.min(audio.duration, audio.currentTime + 5);
    }

    // Audio Playback Speed Control
    function changePlaybackSpeed(event) {
        const select = event.target;
        const audio = select.closest('.audio-controls').querySelector('audio');
        if (audio) {
            audio.playbackRate = parseFloat(select.value);
        }
    }
    
    // Highlight radio button selections
    function highlightOption(radio) {
        const optionsContainer = radio.closest('.options');
        if (optionsContainer) {
            optionsContainer.querySelectorAll('label').forEach(label => {
                label.classList.remove('selected-option');
            });
            if (radio.checked) {
                radio.closest('label').classList.add('selected-option');
            }
        }
    }
    
    // Fix audio player for mobile - ensure audio is interactive
    document.addEventListener('DOMContentLoaded', function() {
        const audioElements = document.querySelectorAll('.audio-element');
        audioElements.forEach(audio => {
            audio.style.pointerEvents = 'auto';
            audio.style.touchAction = 'auto';
        });
    });
    
    // Retry test - clear all answers and reset form
    function retryTest(button) {
        const form = button.closest('form') || document.getElementById('test-form');
        if (!form) return;
        
        // Clear all radio buttons and text inputs
        const radioButtons = form.querySelectorAll('input[type="radio"]');
        radioButtons.forEach(radio => {
            radio.checked = false;
            const label = radio.closest('label');
            if (label) label.classList.remove('selected-option');
        });
        
        const textInputs = form.querySelectorAll('input[type="text"]');
        textInputs.forEach(input => {
            input.value = '';
            input.removeAttribute('readonly');
        });

        const textareas = form.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.value = '';
            textarea.removeAttribute('readonly');
        });
        
        // Stop all audio players and reset speed
        const audioElements = form.querySelectorAll('audio');
        audioElements.forEach(audio => {
            audio.pause();
            audio.currentTime = 0;
            audio.playbackRate = 1.0;
        });

        const speedSelects = form.querySelectorAll('.speed-select');
        speedSelects.forEach(select => {
            select.value = '1.0';
        });
        
        // Remove results summary
        const resultsSummary = document.querySelector('.results-summary');
        if (resultsSummary) {
            resultsSummary.remove();
        }
        
        // Remove result classes from questions
        const questionBoxes = form.querySelectorAll('.question-box');
        questionBoxes.forEach(box => {
            box.classList.remove('correct-answer', 'wrong-answer');
        });
        
        // Remove correct answer texts/elements
        const correctAnswerTexts = form.querySelectorAll('.correct-answer-text');
        correctAnswerTexts.forEach(text => {
            text.remove();
        });

        // For writing part 1 success markers
        const successMarkers = form.querySelectorAll('[style*="color: #4CAF50"]');
        successMarkers.forEach(marker => {
            if (marker.textContent.includes('✓') || marker.textContent.includes('Chính xác')) {
                marker.remove();
            }
        });
        
        // Scroll to top smoothly
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    </script>
</body>
</html>
