import './bootstrap';

import Alpine from 'alpinejs';
import pdfThumb from './pdf-thumb';
import fileTable from './file-table';

window.Alpine = Alpine;

Alpine.plugin(pdfThumb);
Alpine.data('fileTable', fileTable);

Alpine.start();
