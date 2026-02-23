const JPEG_QUALITY = 0.92;
const THUMBNAIL_QUALITY = 0.7;
const THUMBNAIL_MAX_SIZE = 200;
const MAX_OUTPUT_DIMENSION = 4096;
const CORNER_NAMES = [
    'topLeftCorner',
    'topRightCorner',
    'bottomLeftCorner',
    'bottomRightCorner',
];

function distanceBetween(p1, p2) {
    return Math.hypot(p1.x - p2.x, p1.y - p2.y);
}

function isTouchDevice() {
    return 'ontouchstart' in window;
}

function getPointerPosition(event) {
    const touch = event.touches ? event.touches[0] : event;

    return { clientX: touch.clientX, clientY: touch.clientY };
}

function findPaperContour(img) {
    const imgGray = new cv.Mat();
    cv.Canny(img, imgGray, 50, 200);

    const imgBlur = new cv.Mat();
    cv.GaussianBlur(
        imgGray,
        imgBlur,
        new cv.Size(3, 3),
        0,
        0,
        cv.BORDER_DEFAULT,
    );

    const imgThresh = new cv.Mat();
    cv.threshold(imgBlur, imgThresh, 0, 255, cv.THRESH_OTSU);

    const contours = new cv.MatVector();
    const hierarchy = new cv.Mat();
    cv.findContours(
        imgThresh,
        contours,
        hierarchy,
        cv.RETR_CCOMP,
        cv.CHAIN_APPROX_SIMPLE,
    );

    let maxArea = 0;
    let maxContourIndex = -1;
    for (let i = 0; i < contours.size(); ++i) {
        const contourArea = cv.contourArea(contours.get(i));
        if (contourArea > maxArea) {
            maxArea = contourArea;
            maxContourIndex = i;
        }
    }

    const maxContour =
        maxContourIndex >= 0 ? contours.get(maxContourIndex) : null;

    imgGray.delete();
    imgBlur.delete();
    imgThresh.delete();
    contours.delete();
    hierarchy.delete();

    return maxContour;
}

function getCornerPoints(contour) {
    const rect = cv.minAreaRect(contour);
    const center = rect.center;

    let topLeftCorner, topRightCorner, bottomLeftCorner, bottomRightCorner;
    let topLeftDist = 0,
        topRightDist = 0,
        bottomLeftDist = 0,
        bottomRightDist = 0;

    for (let i = 0; i < contour.data32S.length; i += 2) {
        const point = { x: contour.data32S[i], y: contour.data32S[i + 1] };
        const dist = distanceBetween(point, center);

        if (point.x < center.x && point.y < center.y && dist > topLeftDist) {
            topLeftCorner = point;
            topLeftDist = dist;
        } else if (
            point.x > center.x &&
            point.y < center.y &&
            dist > topRightDist
        ) {
            topRightCorner = point;
            topRightDist = dist;
        } else if (
            point.x < center.x &&
            point.y > center.y &&
            dist > bottomLeftDist
        ) {
            bottomLeftCorner = point;
            bottomLeftDist = dist;
        } else if (
            point.x > center.x &&
            point.y > center.y &&
            dist > bottomRightDist
        ) {
            bottomRightCorner = point;
            bottomRightDist = dist;
        }
    }

    return {
        topLeftCorner,
        topRightCorner,
        bottomLeftCorner,
        bottomRightCorner,
    };
}

function extractPaperWithCorners(image, resultWidth, resultHeight, corners) {
    const outputCanvas = document.createElement('canvas');
    const img = cv.imread(image);

    const {
        topLeftCorner,
        topRightCorner,
        bottomLeftCorner,
        bottomRightCorner,
    } = corners;

    const dsize = new cv.Size(resultWidth, resultHeight);
    const srcTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
        topLeftCorner.x,
        topLeftCorner.y,
        topRightCorner.x,
        topRightCorner.y,
        bottomLeftCorner.x,
        bottomLeftCorner.y,
        bottomRightCorner.x,
        bottomRightCorner.y,
    ]);
    const dstTri = cv.matFromArray(4, 1, cv.CV_32FC2, [
        0,
        0,
        resultWidth,
        0,
        0,
        resultHeight,
        resultWidth,
        resultHeight,
    ]);

    const M = cv.getPerspectiveTransform(srcTri, dstTri);
    const warpedDst = new cv.Mat();
    cv.warpPerspective(
        img,
        warpedDst,
        M,
        dsize,
        cv.INTER_LINEAR,
        cv.BORDER_CONSTANT,
        new cv.Scalar(),
    );

    cv.imshow(outputCanvas, warpedDst);

    img.delete();
    warpedDst.delete();
    srcTri.delete();
    dstTri.delete();
    M.delete();

    return outputCanvas;
}

function applyBrightnessContrast(ctx, width, height, brightness, contrast) {
    const imageData = ctx.getImageData(0, 0, width, height);
    const data = imageData.data;
    const brightnessFactor = brightness / 100;
    const contrastFactor = contrast / 100;

    for (let i = 0; i < data.length; i += 4) {
        for (let c = 0; c < 3; c++) {
            let value = data[i + c] * brightnessFactor;
            value = contrastFactor * (value - 128) + 128;
            data[i + c] = Math.max(0, Math.min(255, Math.round(value)));
        }
    }

    ctx.putImageData(imageData, 0, 0);
}

export default ($wire) => ({
    scannedDocuments: [],
    originalImage: null,
    cornerPoints: null,
    draggingCorner: null,
    isEditing: false,
    isUploading: false,
    uploadProgress: 0,
    uploadTotal: 0,
    isProcessing: false,
    openCvReady: false,
    brightness: 100,
    contrast: 100,
    detectionFailed: false,

    get hasDocuments() {
        return this.scannedDocuments.length > 0;
    },

    get isNativeApp() {
        return !!window.nuxbeBridge?.capturePhoto;
    },

    async loadOpenCv() {
        if (this.openCvReady) {
            return;
        }

        if (window.cv?.Mat) {
            this.openCvReady = true;

            return;
        }

        await import('@techstark/opencv-js');
        this.openCvReady = true;
    },

    async captureFromNativeBridge(method) {
        const result = await window.nuxbeBridge[method]();
        if (result.success) {
            this.processImage(
                'data:image/' + result.format + ';base64,' + result.base64,
            );
        }
    },

    async captureFromCamera() {
        if (this.isNativeApp) {
            this.captureFromNativeBridge('capturePhoto');
        } else {
            this.$refs.cameraInput.click();
        }
    },

    async pickFromGallery() {
        if (this.isNativeApp) {
            this.captureFromNativeBridge('pickPhoto');
        } else {
            this.$refs.fileInput.click();
        }
    },

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            this.processImage(e.target.result);
        };
        reader.readAsDataURL(file);
        event.target.value = '';
    },

    async processImage(imageSrc) {
        this.isProcessing = true;
        this.isEditing = true;
        this.brightness = 100;
        this.contrast = 100;
        this.detectionFailed = false;
        this.originalImage = imageSrc;
        this.cornerPoints = null;

        let openCvLoaded = false;
        try {
            await this.loadOpenCv();
            openCvLoaded = true;
        } catch (error) {
            // OpenCV is optional — scanner falls back to manual corner selection
            console.error('OpenCV load failed:', error);
        }

        const img = new Image();
        img.onload = () => {
            let corners = null;

            if (openCvLoaded) {
                const mat = cv.imread(img);
                const contour = findPaperContour(mat);

                if (contour) {
                    const detected = getCornerPoints(contour);
                    if (
                        detected.topLeftCorner &&
                        detected.topRightCorner &&
                        detected.bottomLeftCorner &&
                        detected.bottomRightCorner
                    ) {
                        corners = detected;
                    }
                    contour.delete();
                }

                mat.delete();
            }

            if (!corners) {
                this.detectionFailed = true;
                corners = this.defaultCorners(img);
            }

            this.cornerPoints = corners;
            this.isProcessing = false;
            this.$nextTick(() => this.drawCorners());
        };
        img.onerror = () => {
            this.closeEditor();
        };
        img.src = imageSrc;
    },

    defaultCorners(img) {
        const w = img.naturalWidth;
        const h = img.naturalHeight;
        const margin = Math.min(w, h) * 0.05;

        return {
            topLeftCorner: { x: margin, y: margin },
            topRightCorner: { x: w - margin, y: margin },
            bottomLeftCorner: { x: margin, y: h - margin },
            bottomRightCorner: { x: w - margin, y: h - margin },
        };
    },

    drawCorners() {
        const canvas = this.$refs.cornerCanvas;
        const img = this.$refs.originalImage;
        if (!canvas || !img || !this.cornerPoints) {
            return;
        }

        canvas.width = img.clientWidth;
        canvas.height = img.clientHeight;

        const scaleX = img.clientWidth / img.naturalWidth;
        const scaleY = img.clientHeight / img.naturalHeight;

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const points = [
            this.cornerPoints.topLeftCorner,
            this.cornerPoints.topRightCorner,
            this.cornerPoints.bottomRightCorner,
            this.cornerPoints.bottomLeftCorner,
        ];

        const drawPath = () => {
            ctx.beginPath();
            points.forEach((point, i) => {
                const x = point.x * scaleX;
                const y = point.y * scaleY;
                if (i === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            ctx.closePath();
        };

        ctx.fillStyle = 'rgba(59, 130, 246, 0.15)';
        drawPath();
        ctx.fill();

        ctx.strokeStyle = '#3B82F6';
        ctx.lineWidth = 2;
        drawPath();
        ctx.stroke();

        const handleRadius = isTouchDevice() ? 16 : 10;
        points.forEach((point) => {
            const x = point.x * scaleX;
            const y = point.y * scaleY;
            ctx.beginPath();
            ctx.arc(x, y, handleRadius, 0, 2 * Math.PI);
            ctx.fillStyle = '#3B82F6';
            ctx.fill();
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.stroke();
        });
    },

    startDrag(event) {
        const canvas = this.$refs.cornerCanvas;
        const img = this.$refs.originalImage;
        if (!canvas || !img || !this.cornerPoints) {
            return;
        }

        const rect = canvas.getBoundingClientRect();
        const pointer = getPointerPosition(event);
        const x = pointer.clientX - rect.left;
        const y = pointer.clientY - rect.top;

        const scaleX = img.clientWidth / img.naturalWidth;
        const scaleY = img.clientHeight / img.naturalHeight;

        const hitRadius = isTouchDevice() ? 30 : 20;
        let minDist = Infinity;

        CORNER_NAMES.forEach((name) => {
            const point = this.cornerPoints[name];
            const dist = distanceBetween(
                { x, y },
                { x: point.x * scaleX, y: point.y * scaleY },
            );
            if (dist < minDist && dist < hitRadius) {
                minDist = dist;
                this.draggingCorner = name;
            }
        });

        if (this.draggingCorner) {
            event.preventDefault();
        }
    },

    moveDrag(event) {
        if (!this.draggingCorner) {
            return;
        }
        event.preventDefault();

        const canvas = this.$refs.cornerCanvas;
        const img = this.$refs.originalImage;
        if (!canvas || !img) {
            return;
        }

        const rect = canvas.getBoundingClientRect();
        const pointer = getPointerPosition(event);
        const x = pointer.clientX - rect.left;
        const y = pointer.clientY - rect.top;

        const scaleX = img.naturalWidth / img.clientWidth;
        const scaleY = img.naturalHeight / img.clientHeight;

        this.cornerPoints[this.draggingCorner] = {
            x: Math.max(0, Math.min(img.naturalWidth, x * scaleX)),
            y: Math.max(0, Math.min(img.naturalHeight, y * scaleY)),
        };

        this.drawCorners();
    },

    stopDrag() {
        this.draggingCorner = null;
    },

    updateFilters() {
        const img = this.$refs.originalImage;
        if (img) {
            img.style.filter = `brightness(${this.brightness}%) contrast(${this.contrast}%)`;
        }
    },

    applyAndAddToQueue() {
        if (!this.cornerPoints || !this.originalImage) {
            return;
        }

        const img = new Image();
        img.onload = () => {
            const {
                topLeftCorner,
                topRightCorner,
                bottomLeftCorner,
                bottomRightCorner,
            } = this.cornerPoints;

            const topWidth = distanceBetween(topLeftCorner, topRightCorner);
            const bottomWidth = distanceBetween(
                bottomLeftCorner,
                bottomRightCorner,
            );
            const leftHeight = distanceBetween(topLeftCorner, bottomLeftCorner);
            const rightHeight = distanceBetween(
                topRightCorner,
                bottomRightCorner,
            );

            let outWidth = Math.round(Math.max(topWidth, bottomWidth));
            let outHeight = Math.round(Math.max(leftHeight, rightHeight));

            if (
                outWidth > MAX_OUTPUT_DIMENSION ||
                outHeight > MAX_OUTPUT_DIMENSION
            ) {
                const scale = Math.min(
                    MAX_OUTPUT_DIMENSION / outWidth,
                    MAX_OUTPUT_DIMENSION / outHeight,
                );
                outWidth = Math.round(outWidth * scale);
                outHeight = Math.round(outHeight * scale);
            }

            const resultCanvas = extractPaperWithCorners(
                img,
                outWidth,
                outHeight,
                this.cornerPoints,
            );

            if (
                Number(this.brightness) !== 100 ||
                Number(this.contrast) !== 100
            ) {
                const ctx = resultCanvas.getContext('2d');
                applyBrightnessContrast(
                    ctx,
                    resultCanvas.width,
                    resultCanvas.height,
                    this.brightness,
                    this.contrast,
                );
            }

            this.scannedDocuments.push({
                id:
                    Date.now().toString(36) +
                    Math.random().toString(36).substring(2),
                dataUrl: resultCanvas.toDataURL('image/jpeg', JPEG_QUALITY),
                thumbnail: this.createThumbnail(resultCanvas),
            });

            this.closeEditor();
        };
        img.src = this.originalImage;
    },

    createThumbnail(canvas) {
        const thumbCanvas = document.createElement('canvas');
        const ratio = Math.min(
            THUMBNAIL_MAX_SIZE / canvas.width,
            THUMBNAIL_MAX_SIZE / canvas.height,
        );
        thumbCanvas.width = canvas.width * ratio;
        thumbCanvas.height = canvas.height * ratio;
        const ctx = thumbCanvas.getContext('2d');
        ctx.drawImage(canvas, 0, 0, thumbCanvas.width, thumbCanvas.height);

        return thumbCanvas.toDataURL('image/jpeg', THUMBNAIL_QUALITY);
    },

    closeEditor() {
        this.isEditing = false;
        this.originalImage = null;
        this.cornerPoints = null;
        this.draggingCorner = null;
        this.detectionFailed = false;
    },

    removeDocument(id) {
        this.scannedDocuments = this.scannedDocuments.filter(
            (doc) => doc.id !== id,
        );
    },

    async uploadAll() {
        if (!this.scannedDocuments.length) {
            return;
        }

        this.isUploading = true;
        this.uploadProgress = 0;
        this.uploadTotal = this.scannedDocuments.length;

        let successCount = 0;
        let errorCount = 0;

        for (const doc of this.scannedDocuments) {
            try {
                const success = await $wire.submitScan(doc.dataUrl);
                if (success) {
                    successCount++;
                } else {
                    errorCount++;
                }
            } catch (error) {
                console.error('Upload failed for document:', error);
                errorCount++;
            }
            this.uploadProgress++;
        }

        await $wire.notifyScanResults(successCount, errorCount);

        if (successCount > 0) {
            this.scannedDocuments = [];
        }

        this.isUploading = false;
    },
});
