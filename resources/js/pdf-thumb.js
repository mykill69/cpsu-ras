/**
 * x-pdf-thumb="url"
 *
 * Renders the first page of a PDF into the <canvas> it is placed on, but only
 * once the card scrolls into view. pdf.js is code-split, so pages without PDFs
 * never download it. On failure the canvas stays transparent and the file-type
 * tile underneath remains visible.
 */
let pdfjsPromise = null;

function loadPdfJs() {
    if (!pdfjsPromise) {
        pdfjsPromise = Promise.all([
            import('pdfjs-dist/build/pdf.min.js'),
            import('pdfjs-dist/build/pdf.worker.min.js?url'),
        ]).then(([pdfjs, worker]) => {
            const lib = pdfjs.default ?? pdfjs;
            lib.GlobalWorkerOptions.workerSrc = worker.default;
            return lib;
        });
    }
    return pdfjsPromise;
}

// Keep the number of PDFs decoding at once small so a folder of 30 PDFs
// doesn't hammer the server (each one is decrypted on request).
const MAX_CONCURRENT = 3;
let active = 0;
const queue = [];

function schedule(job) {
    return new Promise((resolve) => {
        queue.push(() => job().finally(() => {
            active--;
            resolve();
            next();
        }));
        next();
    });
}

function next() {
    while (active < MAX_CONCURRENT && queue.length) {
        active++;
        queue.shift()();
    }
}

async function render(canvas, url) {
    const pdfjs = await loadPdfJs();
    const doc = await pdfjs.getDocument({ url, withCredentials: true, isEvalSupported: false, disableAutoFetch: true, disableStream: true }).promise;
    try {
        const page = await doc.getPage(1);
        const base = page.getViewport({ scale: 1 });
        const targetWidth = Math.max(canvas.clientWidth, 160) * Math.min(window.devicePixelRatio || 1, 2);
        const viewport = page.getViewport({ scale: targetWidth / base.width });

        canvas.width = Math.round(viewport.width);
        canvas.height = Math.round(viewport.height);

        await page.render({ canvasContext: canvas.getContext('2d', { alpha: false }), viewport }).promise;
        canvas.classList.remove('opacity-0');
    } finally {
        doc.destroy();
    }
}

export default function pdfThumb(Alpine) {
    Alpine.directive('pdf-thumb', (el, { expression }, { evaluate, cleanup }) => {
        const url = evaluate(expression);
        if (!url || !('IntersectionObserver' in window)) return;

        const observer = new IntersectionObserver((entries) => {
            if (!entries.some((e) => e.isIntersecting)) return;
            observer.disconnect();
            schedule(() => render(el, url).catch(() => el.remove()));
        }, { rootMargin: '200px' });

        observer.observe(el);
        cleanup(() => observer.disconnect());
    });
}
