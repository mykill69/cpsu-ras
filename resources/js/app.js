import './bootstrap';

import Alpine from 'alpinejs';
import pdfThumb from './pdf-thumb';

window.Alpine = Alpine;

Alpine.plugin(pdfThumb);

Alpine.start();
