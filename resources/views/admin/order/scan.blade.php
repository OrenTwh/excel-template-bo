<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#33c7f4',
                    },
                },
            },
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg p-6 max-w-lg w-full text-center">
        <h1 class="text-2xl font-bold text-primary mb-4">QR Code Scanner</h1>
        <p class="text-gray-600 mb-6">Point your camera at a QR code to scan.</p>

        <!-- Video Stream -->
        <div class="relative border-4 border-primary rounded-lg overflow-hidden">
            <video id="video" autoplay class="w-full h-72 bg-gray-200"></video>
        </div>

        <!-- QR Scan Result -->
        <div id="result" class="mt-4 text-lg font-semibold text-gray-700">
            Scanning...
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const result = document.getElementById('result');
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        let scannedData = null; // Prevent multiple AJAX calls for the same QR code

        // Access user's camera
        navigator.mediaDevices
            .getUserMedia({ video: { facingMode: 'environment' } })
            .then((stream) => {
                video.srcObject = stream;
            })
            .catch((err) => {
                console.error('Error accessing camera: ', err);
                result.textContent = 'Unable to access camera';
                result.classList.add('text-red-500');
            });

        // Start scanning QR code
        video.addEventListener('play', () => {
            const scanQRCode = () => {
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;

                    // Draw video frame to canvas
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);

                    // Get image data from canvas
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);

                    // Decode QR code using jsQR
                    const code = jsQR(imageData.data, imageData.width, imageData.height);

                    if (code && code.data !== scannedData) {
                        scannedData = code.data; // Store scanned data to avoid duplicate AJAX calls
                        result.textContent = `QR Code Data: ${code.data}`;
                        result.classList.add('text-green-500');
                        result.classList.remove('text-gray-700');
                        console.log('QR Code Data: ', code.data);

                        // AJAX call to Laravel backend
                        $.ajax({
                            url: '{{ route("admin.order.scannedOrder") }}',
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}", // CSRF token
                                reference: code.data // QR Code data
                            },
                            success: function(response) {
                                alert('Order updated successfully!');
                                console.log(response);
                            },
                            error: function(xhr, status, error) {
                                alert('Error updating order: ' + xhr.responseJSON?.message || error);
                            }
                        });
                    } else if (!code) {
                        result.textContent = 'Scanning...';
                        result.classList.remove('text-green-500');
                        result.classList.add('text-gray-700');
                    }
                }

                // Repeat scan at intervals
                requestAnimationFrame(scanQRCode);
            };

            scanQRCode();
        });
    </script>
</body>
</html>
