/**
 * Alpine component for the backup tables: search, sort and paging reload only
 * the table (fetched in the background), never the page or the URL.
 *
 *   <div x-data="fileTable('/backup', 'current search')">
 *     ...search input (x-model="q")...
 *     <div x-ref="table" @click="navigate($event)"> table partial </div>
 *   </div>
 *
 * The server saves search/sort in the session and, for these background
 * requests, answers with just the table partial.
 */
export default (baseUrl, initialQuery = '') => ({
    q: initialQuery ?? '',
    loading: false,
    controller: null,

    search() {
        this.load(`${baseUrl}?q=${encodeURIComponent(this.q.trim())}`);
    },

    clear() {
        this.q = '';
        this.search();
        this.$nextTick(() => document.getElementById('file-search')?.focus());
    },

    // Sort headers, pagination and "clear search" links inside the table.
    navigate(event) {
        const link = event.target.closest('a[href]');
        if (!link || link.target || event.ctrlKey || event.metaKey || event.shiftKey) return;

        const url = new URL(link.href, window.location.href);
        if (url.origin + url.pathname !== new URL(baseUrl, window.location.href).href.split('?')[0]) return;

        event.preventDefault();
        if (link.dataset.clearSearch !== undefined) {
            this.clear();
            return;
        }
        this.load(url.href);
    },

    async load(url) {
        this.controller?.abort();
        this.controller = new AbortController();
        this.loading = true;

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                credentials: 'same-origin',
                signal: this.controller.signal,
            });

            if (response.status === 401 || response.status === 419) {
                window.location.reload(); // signed out / session expired
                return;
            }
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            this.$refs.table.innerHTML = await response.text();
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error('Could not load the table', error);
            window.Swal?.fire({ icon: 'error', title: 'Could not load files', text: 'Please try again.', timer: 2500, showConfirmButton: false });
        } finally {
            if (!this.controller.signal.aborted) this.loading = false;
        }
    },
});
