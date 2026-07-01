export function registerPdfDownloadLink(Alpine) {
    Alpine.data('pdfDownloadLink', () => ({
        downloading: false,
        error: null,

        async download(url) {
            if (this.downloading) {
                return;
            }

            this.downloading = true;
            this.error = null;

            try {
                const response = await fetch(url, {
                    headers: { Accept: 'application/pdf' },
                    credentials: 'same-origin',
                });

                const contentType = response.headers.get('content-type') || '';

                if (!response.ok || (!contentType.includes('pdf') && !contentType.includes('octet-stream'))) {
                    throw new Error('pdf_unavailable');
                }

                const blob = await response.blob();
                const disposition = response.headers.get('content-disposition') || '';
                let filename = 'document.pdf';
                const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);

                if (match?.[1]) {
                    filename = match[1].replace(/['"]/g, '');
                }

                const blobUrl = URL.createObjectURL(blob);
                const anchor = document.createElement('a');
                anchor.href = blobUrl;
                anchor.download = filename;
                document.body.appendChild(anchor);
                anchor.click();
                anchor.remove();
                URL.revokeObjectURL(blobUrl);
            } catch {
                this.error = 'Could not download PDF. Finalize the document or try again.';
            } finally {
                this.downloading = false;
            }
        },
    }));
}
