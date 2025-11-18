import './bootstrap';

// Alpine.js - Imported by Livewire automatically, no need to import here
// DO NOT import Alpine here to avoid "multiple instances" error

// Swiper.js
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';

// Make Swiper available globally
window.Swiper = Swiper;

// Drift Zoom (Image Zoom on Hover)
import Drift from 'drift-zoom';
import 'drift-zoom/dist/drift-basic.css';
window.Drift = Drift;

// Spotlight.js (Lightbox Gallery) - Using bundled version
import 'spotlight.js/dist/spotlight.bundle.js';
// Spotlight is now available globally via window.Spotlight
