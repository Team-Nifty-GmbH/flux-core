import { copyFileSync, mkdirSync } from 'node:fs';
import { dirname } from 'node:path';

const src = 'node_modules/@techstark/opencv-js/dist/opencv.js';
const dest = 'dist/opencv.js';

mkdirSync(dirname(dest), { recursive: true });
copyFileSync(src, dest);
